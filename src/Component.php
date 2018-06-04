<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Keboola\Component\BaseComponent;
use Psr\Log\LoggerInterface;

class Component extends BaseComponent
{
    public function run(): void
    {
        /** @var Config $config */
        $config = $this->getConfig();
        /** @var LoggerInterface $logger */
        $logger = $this->getLogger();
        $writer = new S3Writer($config, $logger);
        $writer->execute(getenv('KBC_DATADIR') . '/out/files');
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }
}
