<?php

declare(strict_types=1);

namespace App\Tests\HealthCheck;

use App\CMHub\CMHub;
use App\HealthCheck\CMHubCheck;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\HealthCheck\CMHubCheck
 */
class CMHubCheckTest extends TestCase
{
    /**
     * @var CMHub|ObjectProphecy
     */
    private $cmHub;

    private CMHubCheck $healthCheck;

    /**
     * @var ObjectProphecy|ResponseInterface
     */
    private $response;

    protected function setUp(): void
    {
        $this->cmHub = $this->prophesize(CMHub::class);
        $this->healthCheck = new CMHubCheck($this->cmHub->reveal());
        $this->response = $this->prophesize(ResponseInterface::class);
    }

    /**
     * @covers ::__construct
     * @covers ::check
     * @covers ::validateCMHub
     */
    public function testCheck()
    {
        $this->response->getStatusCode()->shouldBeCalled()->willReturn(200);
        $this->cmHub->getAvailability(Argument::any(), Argument::any(), Argument::any())->willReturn($this->response->reveal());

        $this->assertInstanceOf(Success::class, $this->healthCheck->check());
    }

    /**
     * @covers ::__construct
     * @covers ::check
     * @covers ::validateCMHub
     */
    public function testCheckWillFail()
    {
        $this->cmHub->getAvailability(Argument::any(), Argument::any(), Argument::any())->willReturn([]);

        $this->assertInstanceOf(Failure::class, $this->healthCheck->check());
    }

    /**
     * @covers ::__construct
     * @covers ::check
     * @covers ::validateCMHub
     */
    public function testCheckWillThrowException()
    {
        $this->response->getStatusCode()->shouldBeCalled()->willReturn(400);
        $this->cmHub->getAvailability(Argument::any(), Argument::any(), Argument::any())->willReturn($this->response->reveal());

        $check = $this->healthCheck->check();

        $this->assertInstanceOf(Failure::class, $check);
        $this->assertEquals('GetAvailability has failed.', $check->getMessage());
    }
}
