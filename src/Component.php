<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Keboola\Component\BaseComponent;
use Keboola\Component\Logger;

class Component extends BaseComponent
{
    public function run(): void
    {
        $streamHandler = new \Monolog\Handler\StreamHandler('php://stdout');
        $streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter("%message%\n"));
        $logger = new Logger([$streamHandler]);
        $writer = new S3Writer($this->getConfig(), $logger);
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
