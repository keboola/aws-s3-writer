<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests;

use DateTimeImmutable;
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


    /**
     * @dataProvider configWithPrefixReplacementDataProvider
     */
    public function testConfigWithPrefixReplacement(string $prefix, string $expectedPrefix): void
    {
        $configArray = [
            'parameters' => [
                'accessKeyId' => 'key',
                '#secretAccessKey' => 'secret',
                'bucket' => 'bucket',
                'prefix' => $prefix,
            ],
        ];
        $config = new Config($configArray, new ConfigDefinition());

        $this->assertSame('key', $config->getAccessKeyId());
        $this->assertSame('secret', $config->getSecretAccessKey());
        $this->assertSame('bucket', $config->getBucket());
        $this->assertSame($expectedPrefix, $config->getPrefix());
    }

    /**
     * @return array<array<string, string>>
     */
    public static function configWithPrefixReplacementDataProvider(): array
    {
        return [
            [
                'prefix' => '{date}_some_random_prefix',
                'expectedPrefix' => (new DateTimeImmutable())->format('Ymd') . '_some_random_prefix',
            ],
            [
                'prefix' => '{time}_some_great_prefix',
                'expectedPrefix' => (new DateTimeImmutable())->format('His') . '_some_great_prefix',
            ],
            [
                'prefix' => '{timestamp}_some_another_prefix',
                'expectedPrefix' => (new DateTimeImmutable())->format('YmdHis') . '_some_another_prefix',
            ],
        ];
    }
}
