<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Helper\EaiTransactionId;
use App\Logger\Processor\EaiTransactionProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Logger\Processor\EaiTransactionProcessor
 */
class EaiTransactionProcessorTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $eaiTransactionId;

    private EaiTransactionProcessor $eaiTransactionProcessor;

    public function setUp(): void
    {
        $this->eaiTransactionId = $this->prophesize(EaiTransactionId::class);
        $this->eaiTransactionProcessor = new EaiTransactionProcessor(
            $this->eaiTransactionId->reveal()
        );
    }

    public function testAddInfo(): void
    {
        $this->eaiTransactionId->getTransactionId()->willReturn('eai-transaction-is-1234567');
        $this->assertEquals(
            [
                'test' => 'test2',
                'context' => [
                    'eai_transaction_id' => 'eai-transaction-is-1234567',
                ],
                'extra' => [
                    'eai_transaction_id' => 'eai-transaction-is-1234567',
                ],
            ],
            $this->eaiTransactionProcessor->__invoke(['test' => 'test2'])
        );
    }

    public function testAddInfoWithExistingContext(): void
    {
        $this->eaiTransactionId->getTransactionId()->shouldNotBeCalled();
        $this->assertEquals(
            [
                'test' => 'test2',
                'context' => [
                    'eai_transaction_id' => 'eai-transaction-is-1234567',
                ],
                'extra' => [
                    'eai_transaction_id' => 'eai-transaction-is-1234567',
                ],
            ],
            $this->eaiTransactionProcessor->__invoke(['test' => 'test2', 'context' => ['eai_transaction_id' => 'eai-transaction-is-1234567']])
        );
    }

    public function testAddInfoWithNullEaiTransactionId(): void
    {
        $this->eaiTransactionId->getTransactionId()->willReturn(null);
        $this->assertEquals(
            ['test' => 'test2', 'test2' => 'test3'],
            $this->eaiTransactionProcessor->__invoke(['test' => 'test2', 'test2' => 'test3'])
        );
    }
}
