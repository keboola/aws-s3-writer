<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests\Functional;

use Keboola\Component\UserException;
use Keboola\S3Writer\Config;
use Keboola\S3Writer\ConfigDefinition;
use Keboola\S3Writer\S3Writer;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class ExceptionsTest extends FunctionalTestCase
{
    public function testInvalidBucket(): void
    {
        self::expectException(UserException::class);
        self::expectExceptionMessage('404 Not Found (NoSuchBucket)');
        self::expectExceptionMessage('The specified bucket does not exist');
        self::expectExceptionMessage(getenv(self::AWS_S3_BUCKET_ENV) . '_invalid');

        $config = new Config([
            'parameters' => [
                'accessKeyId' => getenv(self::AWS_S3_ACCESS_KEY_ID_ENV),
                '#secretAccessKey' => getenv(self::AWS_S3_SECRET_ACCESS_KEY_ENV),
                'bucket' => getenv(self::AWS_S3_BUCKET_ENV) . '_invalid',
            ],
        ], new ConfigDefinition());

        $writer = new S3Writer(
            $config,
            (new Logger('tests'))->setHandlers([new TestHandler()]),
        );
        $writer->execute('/tmp');
    }

    public function testInvalidCredentials(): void
    {
        self::expectException(UserException::class);
        self::expectExceptionMessage('Invalid credentials or permissions.');

        $config = new Config([
            'parameters' => [
                'accessKeyId' => getenv(self::AWS_S3_ACCESS_KEY_ID_ENV),
                '#secretAccessKey' => getenv(self::AWS_S3_SECRET_ACCESS_KEY_ENV) . '_invalid',
                'bucket' => getenv(self::AWS_S3_BUCKET_ENV),
            ],
        ], new ConfigDefinition());

        $writer = new S3Writer(
            $config,
            (new Logger('tests'))->setHandlers([new TestHandler()]),
        );
        $writer->execute('/tmp');
    }
}
