<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests\Functional;

use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

abstract class FunctionalTestCase extends TestCase
{
    protected const string AWS_S3_BUCKET_ENV = 'AWS_S3_BUCKET';
    protected const string AWS_S3_ACCESS_KEY_ID_ENV = 'WRITER_AWS_ACCESS_KEY_ID';
    protected const string FIXTURES_AWS_S3_ACCESS_KEY_ID_ENV = 'FIXTURES_AWS_ACCESS_KEY_ID';
    protected const string AWS_S3_SECRET_ACCESS_KEY_ENV = 'WRITER_AWS_SECRET_ACCESS_KEY';
    protected const string FIXTURES_AWS_S3_SECRET_ACCESS_KEY_ENV = 'FIXTURES_AWS_SECRET_ACCESS_KEY';
    protected const string AWS_REGION_ENV = 'AWS_REGION';
    protected const string ACCOUNT_ID_ENV = 'ACCOUNT_ID';
    protected const string ROLE_NAME_ENV = 'ROLE_NAME';

    protected function tearDown(): void
    {
        parent::tearDown();
        Process::fromShellCommandline('php ' . __DIR__ . '/../purgeS3.php')->mustRun();
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
