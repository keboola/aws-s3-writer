<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getAccessKeyId() : string
    {
        return $this->getValue(['parameters', 'accessKeyId']);
    }

    public function getSecretAccessKey() : string
    {
        return $this->getValue(['parameters', '#secretAccessKey']);
    }

    public function getBucket() : string
    {
        return $this->getValue(['parameters', 'bucket']);
    }

    public function getPrefix() : string
    {
        return $this->getValue(['parameters', 'prefix'], '');
    }
}
