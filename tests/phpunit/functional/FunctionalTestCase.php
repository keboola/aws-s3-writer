<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests\Functional;

use Aws\S3\S3Client;
use Symfony\Component\Process\Process;

abstract class FunctionalTestCase extends \PHPUnit\Framework\TestCase
{
    protected const AWS_S3_BUCKET_ENV = 'AWS_S3_BUCKET';
    protected const AWS_S3_ACCESS_KEY_ID_ENV = 'WRITER_AWS_ACCESS_KEY_ID';
    protected const FIXTURES_AWS_S3_ACCESS_KEY_ID_ENV = 'FIXTURES_AWS_ACCESS_KEY_ID';
    protected const AWS_S3_SECRET_ACCESS_KEY_ENV = 'WRITER_AWS_SECRET_ACCESS_KEY';
    protected const FIXTURES_AWS_S3_SECRET_ACCESS_KEY_ENV = 'FIXTURES_AWS_SECRET_ACCESS_KEY';
    protected const AWS_REGION_ENV = 'AWS_REGION';

    protected function tearDown(): void
    {
        parent::tearDown();
        (new Process('php ' . __DIR__ . '/../purgeS3.php'))->mustRun();
    }

    protected function getFixturesClient(): S3Client
    {
        return new S3Client([
            'region' => getenv(self::AWS_REGION_ENV),
            'version' => '2006-03-01',
            'credentials' => [
                'key' => getenv(self::FIXTURES_AWS_S3_ACCESS_KEY_ID_ENV),
                'secret' => getenv(self::FIXTURES_AWS_S3_SECRET_ACCESS_KEY_ENV),
            ],
        ]);
    }
}
