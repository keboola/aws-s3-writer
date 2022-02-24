<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Aws\S3\Exception\S3Exception;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use Aws\S3\S3MultiRegionClient;
use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class S3Writer
{
    /**
     *
     */
    private const CHUNK_SIZE = 50;
    /**
     *
     */
    private const MAX_RETRIES_PER_CHUNK = 50;

    private const MAX_LOG_EVENTS = 100;

    /**
     * @var Config
     */
    private $config;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Application constructor.
     */
    public function __construct(Config $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Runs data extraction
     *
     * @throws \Exception
     * @throws UserException
     */
    public function execute(string $sourcePath): void
    {
        try {
            $client = new S3MultiRegionClient(
                [
                    'version' => '2006-03-01',
                    'credentials' => [
                        'key' => $this->config->getAccessKeyId(),
                        'secret' => $this->config->getSecretAccessKey(),
                    ],
                ]
            );
            $region = $client->getBucketLocation(['Bucket' => $this->config->getBucket()])->get('LocationConstraint');

            $options = [
                'region' => $region,
                'version' => '2006-03-01',
                'credentials' => [
                    'key' => $this->config->getAccessKeyId(),
                    'secret' => $this->config->getSecretAccessKey(),
                ],
            ];

            $client = new S3Client($options);

            $relativePathnames = [];
            $finder = (new Finder())->in($sourcePath)->files();
            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                $relativePathnames[] = $file->getRelativePathname();
            }

            // split all slices into batch chunks and upload them separately
            $chunks = ceil(count($relativePathnames) / self::CHUNK_SIZE);
            $counterUploadedFiles = 0;
            $onePercentOfFiles = (int) floor(count($relativePathnames)/100);
            for ($i = 0; $i < $chunks; $i++) {
                $chunk = array_slice($relativePathnames, $i * self::CHUNK_SIZE, self::CHUNK_SIZE);
                // Initialize promises
                $promises = [];
                /** @var SplFileInfo $file */
                foreach ($chunk as $fileRelativePathname) {
                    $counterUploadedFiles++;
                    $s3Key = $this->getS3Key($fileRelativePathname);
                    if (count($relativePathnames) < self::MAX_LOG_EVENTS) {
                        $this->logger->info("Starting upload of file {$fileRelativePathname} to {$s3Key}");
                    } elseif (is_int($counterUploadedFiles/$onePercentOfFiles)) {
                        $this->logger->info(sprintf("Uploaded %d%% files.", $counterUploadedFiles/$onePercentOfFiles));
                    }
                    /*
                     * Cannot upload empty files using multipart: https://github.com/aws/aws-sdk-php/issues/1429
                     * Upload them directly immediately and continue to next part in the chunk.
                     */
                    if (filesize($sourcePath . '/' . $fileRelativePathname) === 0) {
                        $fh = fopen($sourcePath . '/' . $fileRelativePathname, 'r');
                        $putParams = array(
                            'Bucket' => $this->config->getBucket(),
                            'Key' => $s3Key,
                            'Body' => $fh,
                            'ContentDisposition' =>
                                sprintf('attachment; filename=%s;', basename($fileRelativePathname)),
                        );
                        $client->putObject($putParams);
                        continue;
                    }
                    $uploader = $this->multipartUploaderFactory(
                        $client,
                        $sourcePath . '/' . $fileRelativePathname,
                        $this->config->getBucket(),
                        $this->getS3Key($fileRelativePathname)
                    );
                    $promises[$fileRelativePathname] = $uploader->promise();
                }
                /*
                 * In case of an upload failure (\Aws\Exception\MultipartUploadException) there is no sane way of
                 * resuming failed uploads, the exception returns state for a single failed upload and I don't know
                 * which one it is. So I need to iterate over all promises and retry all rejected promises
                 * from scratch.
                 */
                $finished = false;
                $retries = 0;
                do {
                    try {
                        \GuzzleHttp\Promise\unwrap($promises);
                        $finished = true;
                    } catch (\Aws\Exception\MultipartUploadException $e) {
                        $retries++;
                        if ($retries >= self::MAX_RETRIES_PER_CHUNK) {
                            throw new UserException('Exceeded maximum number of retries per chunk upload');
                        }
                        $unwrappedPromises = $promises;
                        $promises = [];
                        /**
                         * @var \GuzzleHttp\Promise\Promise $promise
                         */
                        foreach ($unwrappedPromises as $fileRelativePathname => $promise) {
                            if ($promise->getState() == 'rejected') {
                                $this->logger->info("Retrying upload of file {$fileRelativePathname}");
                                $uploader = $this->multipartUploaderFactory(
                                    $client,
                                    $sourcePath . '/' . $fileRelativePathname,
                                    $this->config->getBucket(),
                                    $this->getS3Key($fileRelativePathname)
                                );
                                $promises[$fileRelativePathname] = $uploader->promise();
                            }
                        }
                    }
                } while (!$finished);
            }
        } catch (S3Exception $e) {
            throw ExceptionFactory::fromS3Exception($e);
        }
    }

    private function multipartUploaderFactory(
        S3Client $s3Client,
        string $filePath,
        string $bucket,
        string $key,
        ?string $friendlyName = null
    ) : MultipartUploader {
        $uploaderOptions = [
            'Bucket' => $bucket,
            'Key' => $key,
        ];
        $beforeInitiateCommands = [];
        if (!empty($friendlyName)) {
            $beforeInitiateCommands['ContentDisposition'] = sprintf('attachment; filename=%s;', $friendlyName);
        }
        if (count($beforeInitiateCommands)) {
            $uploaderOptions['before_initiate'] = function ($command) use ($beforeInitiateCommands) : void {
                foreach ($beforeInitiateCommands as $key => $value) {
                    $command[$key] = $value;
                }
            };
        }
        return new MultipartUploader($s3Client, $filePath, $uploaderOptions);
    }

    /**
     *
     * Concats prefix (without initial forwardslash) and relative path name to the file
     */
    private function getS3Key(string $relativePathname) : string
    {
        return ltrim($this->config->getPrefix(), '/') . $relativePathname;
    }
}
