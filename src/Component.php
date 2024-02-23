<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;

class Component extends BaseComponent
{
    private const string ACTION_GET_EXTERNAL_ID = 'getExternalId';

    private const string ACTION_RUN = 'run';

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function run(): void
    {
        /** @var Config $config */
        $config = $this->getConfig();
        $logger = $this->getLogger();
        $writer = new S3Writer($config, $logger);
        $writer->execute($this->getDataDir() . '/in/files');
    }

    /**
     * @return array<string, string>
     */
    public function getExternalIdAction(): array
    {
        /** @var Config $config */
        $config = $this->getConfig();

        $extractor = new S3Writer($config, $this->getLogger());

        return ['external-id' => $extractor->getExternalId()];
    }

    /**
     * @return array<string, string>
     */
    protected function getSyncActions(): array
    {
        return [
            self::ACTION_GET_EXTERNAL_ID => 'getExternalIdAction',
        ];
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function getConfigDefinitionClass(): string
    {
        $action = $this->getRawConfig()['action'] ?? self::ACTION_RUN;
        return match ($action) {
            self::ACTION_RUN => ConfigDefinition::class,
            self::ACTION_GET_EXTERNAL_ID => GetExternalIdDefinition::class,
            default => throw new UserException(sprintf('Unexpected action "%s"', $action)),
        };
    }
}
