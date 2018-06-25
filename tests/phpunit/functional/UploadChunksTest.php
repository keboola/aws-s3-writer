<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests\Functional;

use Keboola\S3Writer\Config;
use Keboola\S3Writer\ConfigDefinition;
use Keboola\S3Writer\S3Writer;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class UploadChunksTest extends FunctionalTestCase
{

    /**
     * @dataProvider uploadArgumentsProvider
     */
    public function testUploadFilesToRoot(): void
    {
        $testHandler = new TestHandler();
        $config = new Config([
            "parameters" => [
                "accessKeyId" => getenv(self::AWS_S3_ACCESS_KEY_ID_ENV),
                "#secretAccessKey" => getenv(self::AWS_S3_SECRET_ACCESS_KEY_ENV),
                "bucket" => getenv(self::AWS_S3_BUCKET_ENV),
            ],
        ], new ConfigDefinition());
        $writer = new S3Writer($config, (new Logger('test'))->pushHandler($testHandler));
        $writer->execute(__DIR__ . "/data/chunks");

        self::assertCount(100, $testHandler->getRecords());
        $client = $this->getFixturesClient();
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'file0.csv'));
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'file99.csv'));
    }
}
