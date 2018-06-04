<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3MultiRegionClient;
use GuzzleHttp\Exception\ClientException;
use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class S3Writer
{
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
     * @throws \Exception
     */
    public function execute(string $sourcePath): void
    {
        try {
            $client = new S3MultiRegionClient([
                'version' => '2006-03-01',
                'credentials' => [
                    'key' => $this->config->getAccessKeyId(),
                    'secret' => $this->config->getSecretAccessKey(),
                ],
            ]);
            $region = $client->getBucketLocation(["Bucket" => $this->config->getBucket()])->get('LocationConstraint');
            $client = new S3Client([
                'region' => $region,
                'version' => '2006-03-01',
                'credentials' => [
                    'key' => $this->config->getAccessKeyId(),
                    'secret' => $this->config->getSecretAccessKey(),
                ],
            ]);

            $finder = (new Finder())->in($sourcePath)->files();
            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                // Remove initial forwardslash
                $prefix = $this->config->getPrefix();
                if (substr($prefix, 0, 1) == '/') {
                    $prefix = substr($prefix, 1);
                }
                $key = $prefix . $file->getRelativePathname();
                $this->logger->info("Uploading file {$file->getRelativePathname()} to {$key}");
                $client->putObject(
                    [
                        'Bucket' => $this->config->getBucket(),
                        'Key' => $key,
                        'Body' => fopen($file->getPathname(), 'r'),
                    ]
                );
            }
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 403) {
                throw new UserException("Invalid credentials or permissions.", $e->getCode(), $e);
            }
            if ($e->getStatusCode() === 400 || $e->getStatusCode() === 401 || $e->getStatusCode() === 404) {
                if ($e->getPrevious() instanceof ClientException) {
                    /** @var ClientException $previous */
                    $previous = $e->getPrevious();
                    if ($previous->getResponse()) {
                        throw S3WriterException::factory($e);
                    }
                    throw new UserException($previous->getMessage());
                }
                throw new UserException($e->getMessage());
            }
            throw $e;
        }
    }
}
