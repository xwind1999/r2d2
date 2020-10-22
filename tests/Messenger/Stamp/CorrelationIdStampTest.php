<?php

declare(strict_types=1);

namespace App\Tests\Messenger\Stamp;

use App\Messenger\Stamp\CorrelationIdStamp;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Messenger\Stamp\CorrelationIdStamp
 */
class CorrelationIdStampTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $stamp = new CorrelationIdStamp('correlation id');
        $this->assertEquals('correlation id', $stamp->correlationId);
    }
}
