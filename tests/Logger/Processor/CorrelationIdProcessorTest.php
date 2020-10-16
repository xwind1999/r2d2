<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Http\CorrelationId\CorrelationId;
use App\Logger\Processor\CorrelationIdProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Logger\Processor\CorrelationIdProcessor
 */
class CorrelationIdProcessorTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $correlationId;

    private CorrelationIdProcessor $correlationProcessor;

    public function setUp(): void
    {
        $this->correlationId = $this->prophesize(CorrelationId::class);
        $this->correlationProcessor = new CorrelationIdProcessor(
            $this->correlationId->reveal()
        );
    }

    public function testAddInfo(): void
    {
        $this->correlationId->getUuid()->willReturn('correlation-id-is-1234567');
        $this->assertEquals(
            [
                'test' => 'test2',
                'extra' => [
                    'correlation_id' => 'correlation-id-is-1234567',
                ],
                'context' => [
                    'correlation_id' => 'correlation-id-is-1234567',
                ],
            ],
            $this->correlationProcessor->__invoke(['test' => 'test2'])
        );
    }
}
