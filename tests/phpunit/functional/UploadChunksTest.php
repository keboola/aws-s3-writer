<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests\Functional;

use Generator;
use Keboola\S3Writer\Config;
use Keboola\S3Writer\ConfigDefinition;
use Keboola\S3Writer\S3Writer;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class UploadChunksTest extends FunctionalTestCase
{
    /**
     * @dataProvider configProvider
     * @param array<string, array<string, string>> $configArray
     * @throws \Keboola\Component\UserException
     */
    public function testUploadFiles(array $configArray): void
    {
        $testHandler = new TestHandler();
        $config = new Config($configArray, new ConfigDefinition());
        $writer = new S3Writer($config, (new Logger('test'))->pushHandler($testHandler));
        $writer->execute(__DIR__ . '/data/chunks50');

        self::assertCount(50, $testHandler->getRecords());
        self::assertFalse($testHandler->hasInfo('Uploaded 1% files.'));
        self::assertFalse($testHandler->hasInfo('Uploaded 49% files.'));
        self::assertTrue($testHandler->hasInfo('Starting upload of file file49.csv to file49.csv'));
        $client = $this->getFixturesClient();
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'file0.csv'));
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'file49.csv'));
    }

    /**
     * @dataProvider configProvider
     * @param array<string, array<string, string>> $configArray
     * @throws \Keboola\Component\UserException
     */
    public function testUploadLotOfFilesToRoot(array $configArray): void
    {
        $testHandler = new TestHandler();
        $config = new Config($configArray, new ConfigDefinition());

        $writer = new S3Writer($config, (new Logger('test'))->pushHandler($testHandler));
        $writer->execute(__DIR__ . '/data/chunks');

        self::assertCount(100, $testHandler->getRecords());
        self::assertTrue($testHandler->hasInfo('Uploaded 1% files.'));
        self::assertTrue($testHandler->hasInfo('Uploaded 49% files.'));
        self::assertTrue($testHandler->hasInfo('Uploaded 100% files.'));
        self::assertFalse($testHandler->hasInfo('Starting upload of file file49.csv to file49.csv'));
        $client = $this->getFixturesClient();
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'file0.csv'));
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'file49.csv'));
        self::assertTrue($client->doesObjectExist(getenv(self::AWS_S3_BUCKET_ENV), 'file99.csv'));
    }

    public function configProvider(): Generator
    {
        yield 'credentials_login' => [[
            'parameters' => [
                'accessKeyId' => getenv(self::AWS_S3_ACCESS_KEY_ID_ENV),
                '#secretAccessKey' => getenv(self::AWS_S3_SECRET_ACCESS_KEY_ENV),
                'bucket' => getenv(self::AWS_S3_BUCKET_ENV),
            ],
        ]];

        yield 'role_login' => [[
            'parameters' => [
                'loginType' => ConfigDefinition::LOGIN_TYPE_ROLE,
                'roleName' => getenv(self::ROLE_NAME_ENV),
                'accountId' => getenv(self::ACCOUNT_ID_ENV),
                'bucket' => getenv(self::AWS_S3_BUCKET_ENV),
            ],
        ]];
    }
}
