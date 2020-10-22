<?php

declare(strict_types=1);

namespace App\Tests\HealthCheck;

use App\HealthCheck\EAICheck;
use App\Tests\ProphecyTestCase;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Prophecy\Argument;
use Smartbox\ApiRestClient\ApiRestResponse;
use Smartbox\ApiRestClient\Clients\ChecksV0Client;

/**
 * @coversDefaultClass \App\HealthCheck\EAICheck
 */
class EAICheckTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     * @covers ::check
     * @covers ::validateEai
     */
    public function testCheck()
    {
        $responseApi = new ApiRestResponse();
        $responseApi->setStatusCode(204);

        $checkClient = $this->prophesize(ChecksV0Client::$class);
        $checkClient->void(Argument::cetera())->willReturn($responseApi);

        $eaiCheck = new EAICheck($checkClient->reveal());
        $response = $eaiCheck->check();

        $this->assertInstanceOf(Success::class, $response);
        $this->assertStringContainsString('204', $response->getMessage());
    }

    /**
     * @covers ::__construct
     * @covers ::check
     * @covers ::validateEai
     * @dataProvider statusCodeProvider
     */
    public function testCheckFails(string $status)
    {
        $responseApi = new ApiRestResponse();
        $responseApi->setStatusCode($status);

        $checkClient = $this->prophesize(ChecksV0Client::$class);
        $checkClient->void(Argument::cetera())->willReturn($responseApi);

        $eaiCheck = new EAICheck($checkClient->reveal());
        $response = $eaiCheck->check();

        $this->assertInstanceOf(Failure::class, $response);
        $this->assertStringContainsString($status, $response->getMessage());
    }

    public function statusCodeProvider()
    {
        yield 'redirect' => ['status' => '307'];
        yield 'authorization_error' => ['status' => '401'];
        yield 'not_found_error' => ['status' => '404'];
        yield 'server_error' => ['status' => '500'];
        yield 'bad_gateway' => ['status' => '502'];
    }

    /**
     * @covers ::__construct
     * @covers ::check
     * @covers ::validateEai
     */
    public function testCheckThrowException()
    {
        $checkClient = $this->prophesize(ChecksV0Client::$class);
        $checkClient->void(Argument::cetera())->willThrow(new \Exception());

        $eaiCheck = new EAICheck($checkClient->reveal());
        $response = $eaiCheck->check();

        $this->assertInstanceOf(Failure::class, $response);
        $this->assertEquals('Unable to contact EAI!', $response->getMessage());
    }
}
