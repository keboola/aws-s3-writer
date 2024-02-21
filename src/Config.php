<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Exception;
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

    public function getLoginType(): string
    {
        return $this->getValue(['parameters', 'loginType']);
    }

    public function getAccountId(): string
    {
        return $this->getValue(['parameters', 'accountId']);
    }

    public function getRoleName(): string
    {
        return $this->getValue(['parameters', 'roleName']);
    }

    public function getKeboolaUserAwsAccessKey(): string
    {
        $accessKey = getenv('KEBOOLA_USER_AWS_ACCESS_KEY');
        if ($accessKey) {
            return $accessKey;
        }
        if (!isset($this->getImageParameters()['KEBOOLA_USER_AWS_ACCESS_KEY'])) {
            throw new Exception('Keboola aws user access key is missing from image parameters');
        }
        return $this->getImageParameters()['KEBOOLA_USER_AWS_ACCESS_KEY'];
    }

    public function getKeboolaUserAwsSecretKey(): string
    {
        $secretKey = (string) getenv('KEBOOLA_USER_AWS_SECRET_KEY');
        if ($secretKey) {
            return $secretKey;
        }
        if (!isset($this->getImageParameters()['#KEBOOLA_USER_AWS_SECRET_KEY'])) {
            throw new Exception('Keboola aws user secret key is missing from image parameters');
        }
        return $this->getImageParameters()['#KEBOOLA_USER_AWS_SECRET_KEY'];
    }
}
