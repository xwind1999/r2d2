<?php

declare(strict_types=1);

namespace App\Tests\HealthCheck;

use App\HealthCheck\QuickDataCheck;
use App\QuickData\QuickData;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class QuickDataCheckTest extends TestCase
{
    public function testCheck()
    {
        $quickData = $this->prophesize(QuickData::class);
        $healthCheck = new QuickDataCheck($quickData->reveal());

        $quickData->getPackage(Argument::any(), Argument::any(), Argument::any())->willReturn(['ListPrestation' => [['PartnerCode' => '123']]]);

        $check = $healthCheck->check();

        $this->assertInstanceOf(Success::class, $check);
    }

    public function testCheckWillFail()
    {
        $quickData = $this->prophesize(QuickData::class);
        $healthCheck = new QuickDataCheck($quickData->reveal());

        $quickData->getPackage(Argument::any(), Argument::any(), Argument::any())->willReturn([]);

        $check = $healthCheck->check();

        $this->assertInstanceOf(Failure::class, $check);
    }

    public function testCheckWillThrowException()
    {
        $quickData = $this->prophesize(QuickData::class);
        $healthCheck = new QuickDataCheck($quickData->reveal());

        $quickData->getPackage(Argument::any(), Argument::any(), Argument::any())->willThrow(new \Exception());

        $check = $healthCheck->check();

        $this->assertInstanceOf(Failure::class, $check);
        $this->assertEquals('Unable to contact QuickData!', $check->getMessage());
    }
}
