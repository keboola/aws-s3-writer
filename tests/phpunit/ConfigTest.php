<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests;

use Keboola\S3Writer\Config;
use Keboola\S3Writer\ConfigDefinition;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testCustomGettersWithDefaults(): void
    {
        $configArray = [
            'parameters' => [
                'accessKeyId' => 'key',
                '#secretAccessKey' => 'secret',
                'bucket' => 'bucket',
            ],
        ];
        $config = new Config($configArray, new ConfigDefinition());

        $this->assertSame('key', $config->getAccessKeyId());
        $this->assertSame('secret', $config->getSecretAccessKey());
        $this->assertSame('bucket', $config->getBucket());
        $this->assertSame('', $config->getPrefix());
    }

    public function testCustomGettersWithoutDefaults(): void
    {
        $configArray = [
            'parameters' => [
                'accessKeyId' => 'key',
                '#secretAccessKey' => 'secret',
                'bucket' => 'bucket',
                'prefix' => 'prefix',
            ],
        ];
        $config = new Config($configArray, new ConfigDefinition());

        $this->assertSame('key', $config->getAccessKeyId());
        $this->assertSame('secret', $config->getSecretAccessKey());
        $this->assertSame('bucket', $config->getBucket());
        $this->assertSame('prefix', $config->getPrefix());
    }
}
