<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests\Functional;

use Keboola\S3Writer\Config;
use Keboola\S3Writer\ConfigDefinition;
use Keboola\S3Writer\S3Writer;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class UploadToRootTest extends FunctionalTestCase
{

    /**
     * @dataProvider uploadArgumentsProvider
     */
    public function testUploadFilesToRoot(bool $initialForwardSlash, string $dataFilesPath): void
    {
        $prefix = "";
        if ($initialForwardSlash) {
            $prefix = "/" . $prefix;
        }
        $testHandler = new TestHandler();
        $config = new Config([
            "parameters" => [
                "loginType" => ConfigDefinition::LOGIN_TYPE_CREDENTIALS,
                "accessKeyId" => getenv(self::AWS_S3_ACCESS_KEY_ID_ENV),
                "#secretAccessKey" => getenv(self::AWS_S3_SECRET_ACCESS_KEY_ENV),
                "bucket" => getenv(self::AWS_S3_BUCKET_ENV),
                "prefix" => $prefix,
            ],
        ], new ConfigDefinition());
        $writer = new S3Writer($config, (new Logger('test'))->pushHandler($testHandler));
        $writer->execute($dataFilesPath);

        self::assertCount(2, $testHandler->getRecords());
        self::assertTrue($testHandler->hasInfoThatContains("Starting upload of file file1.csv to file1.csv"));
        self::assertTrue($testHandler->hasInfoThatContains(
            "Starting upload of file folder/file1.csv to folder/file1.csv"
        ));
        $client = $this->getFixturesClient();
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'file1.csv'));
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'folder/file1.csv'));
    }

    public function uploadArgumentsProvider(): array
    {
        return [
            [true, __DIR__ . "/data/files"],
            [true, __DIR__ . "/data/files-empty"],
            [false, __DIR__ . "/data/files"],
            [false, __DIR__ . "/data/files-empty"],
        ];
    }
}
