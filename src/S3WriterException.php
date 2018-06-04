<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Exception\ClientException;
use Keboola\Component\UserException;

class S3WriterException
{
    /**
     * @return UserException|S3Exception
     */
    public static function fromS3Exception(S3Exception $e)
    {
        if ($e->getStatusCode() === 403) {
            return new UserException("Invalid credentials or permissions.", $e->getCode(), $e);
        }
        if ($e->getStatusCode() === 400 || $e->getStatusCode() === 401 || $e->getStatusCode() === 404) {
            if ($e->getPrevious() instanceof ClientException) {
                /** @var ClientException $previous */
                $previous = $e->getPrevious();
                if ($previous->getResponse()) {
                    $previous = $e->getPrevious();
                    return new UserException(
                        $e->getStatusCode()
                        . " "
                        . $previous->getResponse()->getReasonPhrase()
                        . " ("
                        . $e->getAwsErrorCode()
                        . ")\n"
                        . $previous->getResponse()->getBody()->__toString()
                    );
                }
                return new UserException($previous->getMessage());
            }
            return new UserException($e->getMessage());
        }
        return $e;
    }
}
