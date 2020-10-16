<?php

declare(strict_types=1);

namespace App\Tests\Messenger\Stamp;

use App\Messenger\Stamp\EaiTransactionIdStamp;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Messenger\Stamp\EaiTransactionIdStamp
 */
class EaiTransactionIdStampTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $stamp = new EaiTransactionIdStamp('eai-transaction-id');
        $this->assertEquals('eai-transaction-id', $stamp->eaiTransactionId);
    }
}
