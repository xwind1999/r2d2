<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Logger\Processor\RequestIdProcessor;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;

class RequestIdProcessorTest extends TestCase
{
    public function testAddInfo(): void
    {
        $uuidFactory = $this->prophesize(UuidFactoryInterface::class);
        $uuidInterface = $this->prophesize(UuidInterface::class);
        $uuidInterface->toString()->willReturn('3fa85f64-5717-4562-b3fc-2c963f66afa6');
        $uuidFactory->uuid4()->willReturn($uuidInterface->reveal());
        $processor = new RequestIdProcessor($uuidFactory->reveal());
        $this->assertEquals(
            ['test' => 'test2', 'extra' => ['request_id' => '3fa85f64-5717-4562-b3fc-2c963f66afa6']],
            $processor(['test' => 'test2'])
        );
    }
}
