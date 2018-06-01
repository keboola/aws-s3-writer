<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests\Functional;

use Symfony\Component\Process\Process;

class FunctionalTestCase extends \PHPUnit\Framework\TestCase
{
    protected const AWS_S3_BUCKET_ENV = 'AWS_S3_BUCKET';
    protected const AWS_S3_ACCESS_KEY_ID_ENV = 'WRITER_AWS_ACCESS_KEY_ID';
    protected const AWS_S3_SECRET_ACCESS_KEY_ENV = 'WRITER_AWS_SECRET_ACCESS_KEY';
    protected const AWS_REGION = 'AWS_REGION';

    public function tearDown(): void
    {
        parent::tearDown();
        (new Process('php ' . __DIR__ . '/../purgeS3.php'))->mustRun();
    }
}
