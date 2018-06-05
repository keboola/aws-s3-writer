<?php

declare(strict_types=1);

namespace Keboola\S3Writer\Tests;

use Aws\Command;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Keboola\Component\UserException;
use Keboola\S3Writer\ExceptionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionFactoryTest extends TestCase
{
    public function test403Exception(): void
    {
        /**
         * @var MockObject&S3Exception $stub
         */
        $stub = self::createMock(S3Exception::class);
        $stub
            ->method('getCode')
            ->will(self::returnValue('code'));
        $stub
            ->method('getStatusCode')
            ->will(self::returnValue(403));

        $exception = ExceptionFactory::fromS3Exception($stub);
        self::assertEquals(UserException::class, get_class($exception));
        self::assertEquals("Invalid credentials or permissions.", $exception->getMessage());
    }

    public function test40XPreviousUnknownException(): void
    {
        $previousStub = self::getMockBuilder(\Throwable::class)
            ->setConstructorArgs(['myPreviousException'])
            ->getMock();

        /**
         * @var MockObject&S3Exception $stub
         */
        $stub = self::getMockBuilder(S3Exception::class)
            ->setConstructorArgs(['myException', new Command('test'), [], $previousStub])
            ->getMock();
        $stub
            ->method('getStatusCode')
            ->will(self::returnValue(400));

        $exception = ExceptionFactory::fromS3Exception($stub);
        self::assertEquals(UserException::class, get_class($exception));
        self::assertEquals("myException", $exception->getMessage());
    }

    public function test40XPreviousClientExceptionException(): void
    {
        $previousStub = self::getMockBuilder(ClientException::class)
            ->setConstructorArgs(['myException', new Request('get', 'test')])
            ->getMock();
        $previousStub
            ->method('getResponse')
            ->will(self::returnValue(null));

        /**
         * @var MockObject&S3Exception $stub
         */
        $stub = self::getMockBuilder(S3Exception::class)
            ->setConstructorArgs([null, new Command('test'), [], $previousStub])
            ->getMock();
        $stub
            ->method('getStatusCode')
            ->will(self::returnValue(400));

        $exception = ExceptionFactory::fromS3Exception($stub);
        self::assertEquals(UserException::class, get_class($exception));
        self::assertEquals("myException", $exception->getMessage());
    }

    public function test40XPreviousClientExceptionWithResponseException(): void
    {
        $bodyStub = self::createMock(Stream::class);
        $bodyStub
            ->method('__toString')
            ->will(self::returnValue('Test Error'));

        $responseStub = self::createMock(Response::class);
        $responseStub
            ->method('getReasonPhrase')
            ->will(self::returnValue('Error'));
        $responseStub
            ->method('getBody')
            ->will(self::returnValue($bodyStub));

        $previousStub = self::createMock(ClientException::class);
        $previousStub
            ->method('getResponse')
            ->will(self::returnValue($responseStub));

        /**
         * @var MockObject&S3Exception $stub
         */
        $stub = self::getMockBuilder(S3Exception::class)
            ->setConstructorArgs([null, new Command('test'), [], $previousStub])
            ->getMock();
        $stub
            ->method('getAwsErrorCode')
            ->will(self::returnValue('awsErrorCode'));
        $stub
            ->method('getStatusCode')
            ->will(self::returnValue(400));

        $exception = ExceptionFactory::fromS3Exception($stub);
        self::assertEquals(UserException::class, get_class($exception));
        self::assertEquals("400 Error (awsErrorCode)\nTest Error", $exception->getMessage());
    }

    public function test500Exception(): void
    {
        /**
         * @var MockObject&S3Exception $stub
         */
        $stub = self::createMock(S3Exception::class);
        $stub
            ->method('getCode')
            ->will(self::returnValue('code'));
        $stub
            ->method('getStatusCode')
            ->will(self::returnValue(500));

        $exception = ExceptionFactory::fromS3Exception($stub);
        self::assertEquals($stub, $exception);
    }
}
