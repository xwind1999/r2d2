<?php

declare(strict_types=1);

namespace App\Tests\Messenger\Stamp;

use App\Messenger\Stamp\EaiTransactionIdStamp;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Messenger\Stamp\EaiTransactionIdStamp
 */
class EaiTransactionIdStampTest extends ProphecyTestCase
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
