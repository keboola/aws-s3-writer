<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Exception\ClientException;
use Keboola\Component\UserException;

class S3WriterException
{
    public static function factory(S3Exception $e): UserException
    {
        /** @var ClientException $previous */
        $previous = $e->getPrevious();
        return new UserException(
            $previous->getResponse()->getStatusCode()
            . " "
            . $previous->getResponse()->getReasonPhrase()
            . " ("
            . $e->getAwsErrorCode()
            . ")\n"
            . $previous->getResponse()->getBody()->__toString()
        );
    }
}
