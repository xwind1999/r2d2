<?php

declare(strict_types=1);

namespace App\Tests\Logger\Processor;

use App\Logger\Processor\EaiTransactionProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EaiTransactionProcessorTest extends TestCase
{
    public function testAddInfo(): void
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $request = $this->prophesize(Request::class);
        $headers = $this->prophesize(HeaderBag::class);
        $headers->get('x-transaction-id', null)->willReturn('eai-transaction-is-1234567');
        $request->headers = $headers->reveal();
        $requestStack->getMasterRequest()->willReturn($request->reveal());
        $processor = new EaiTransactionProcessor($requestStack->reveal());
        $this->assertEquals(
            ['test' => 'test2', 'extra' => ['eai_transaction_id' => 'eai-transaction-is-1234567']],
            $processor(['test' => 'test2'])
        );
    }

    public function testAddInfoWithoutRequest(): void
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getMasterRequest()->willReturn(null);
        $processor = new EaiTransactionProcessor($requestStack->reveal());
        $this->assertEquals(
            ['test' => 'test2', 'test2' => 'test3'],
            $processor(['test' => 'test2', 'test2' => 'test3'])
        );
    }

    public function testAddInfoWithoutHeaders(): void
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $request = $this->prophesize(Request::class);
        $request->headers = null;
        $requestStack->getMasterRequest()->willReturn($request->reveal());
        $processor = new EaiTransactionProcessor($requestStack->reveal());
        $this->assertEquals(
            ['test' => 'test2', 'test2' => 'test3'],
            $processor(['test' => 'test2', 'test2' => 'test3'])
        );
    }

    public function testAddInfoWithoutEAIHeaders(): void
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $request = $this->prophesize(Request::class);
        $headers = $this->prophesize(HeaderBag::class);
        $headers->get('x-transaction-id', null)->willReturn(null);
        $request->headers = $headers->reveal();
        $requestStack->getMasterRequest()->willReturn($request->reveal());
        $processor = new EaiTransactionProcessor($requestStack->reveal());
        $this->assertEquals(
            ['test' => 'test2', 'test2' => 'test3'],
            $processor(['test' => 'test2', 'test2' => 'test3'])
        );
    }
}
