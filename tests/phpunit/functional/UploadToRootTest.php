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
     * @dataProvider initialForwardSlashProvider
     */
    public function testUploadToRoot(bool $initialForwardSlash): void
    {
        $prefix = "";
        if ($initialForwardSlash) {
            $prefix = "/" . $prefix;
        }
        $testHandler = new TestHandler();
        $config = new Config([
            "parameters" => [
                "accessKeyId" => getenv(self::AWS_S3_ACCESS_KEY_ID_ENV),
                "#secretAccessKey" => getenv(self::AWS_S3_SECRET_ACCESS_KEY_ENV),
                "bucket" => getenv(self::AWS_S3_BUCKET_ENV),
                "prefix" => $prefix,
            ],
        ], new ConfigDefinition());
        $writer = new S3Writer($config, (new Logger('test'))->pushHandler($testHandler));
        $writer->execute(__DIR__ . "/_data/out/files");

        $this->assertCount(2, $testHandler->getRecords());
        $this->assertTrue($testHandler->hasInfoThatContains("Uploading file file1.csv to file1.csv"));
        $this->assertTrue($testHandler->hasInfoThatContains("Uploading file folder/file1.csv to folder/file1.csv"));
    }

    public function initialForwardSlashProvider(): array
    {
        return [[true], [false]];
    }
}