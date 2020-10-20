<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\EaiTransactionId;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \App\Helper\EaiTransactionId
 */
class EaiTransactionIdTest extends TestCase
{
    /**
     * @var ObjectProphecy|RequestStack
     */
    private $requestStack;

    /**
     * @var ObjectProphecy|Request
     */
    private $request;

    /**
     * @var HeaderBag|ObjectProphecy
     */
    private $headers;

    private EaiTransactionId $eaiTransactionId;

    public function setUp(): void
    {
        $this->requestStack = $this->prophesize(RequestStack::class);
        $this->request = $this->prophesize(Request::class);
        $this->headers = $this->prophesize(HeaderBag::class);
        $this->eaiTransactionId = new EaiTransactionId($this->requestStack->reveal());
    }

    /**
     * @covers ::__construct
     * @covers ::getTransactionId
     * @covers ::loadTransactionId
     */
    public function testGetTransactionId(): void
    {
        $this->headers->get('x-transaction-id', null)->willReturn('eai-transaction-is-1234567');
        $this->request->headers = $this->headers->reveal();
        $this->requestStack->getMasterRequest()->willReturn($this->request->reveal());
        $this->assertEquals(
            'eai-transaction-is-1234567',
            $this->eaiTransactionId->getTransactionId()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getTransactionId
     */
    public function testGetTransactionIdWithoutRequest(): void
    {
        $this->requestStack->getMasterRequest()->willReturn(null);
        $this->assertEquals(
            null,
            $this->eaiTransactionId->getTransactionId()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getTransactionId
     */
    public function testGetTransactionIdWithoutHeaders(): void
    {
        $this->request->headers = $this->headers->reveal();
        $this->requestStack->getMasterRequest()->willReturn($this->request->reveal());
        $this->assertEquals(
            null,
            $this->eaiTransactionId->getTransactionId()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getTransactionId
     */
    public function testGetTransactionIdWithoutEAIHeaders(): void
    {
        $this->headers->get('x-transaction-id', null)->willReturn(null);
        $this->request->headers = $this->headers->reveal();
        $this->requestStack->getMasterRequest()->willReturn($this->request->reveal());
        $this->assertEquals(
            null,
            $this->eaiTransactionId->getTransactionId()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getTransactionId
     * @covers ::resetTransactionIdOverride
     * @covers ::setTransactionIdOverride
     */
    public function testGetTransactionIdWithOverride(): void
    {
        $this->request->headers = $this->headers->reveal();
        $this->headers->get('x-transaction-id', null)->willReturn('original-eai-transaction-id');
        $this->requestStack->getMasterRequest()->willReturn($this->request->reveal());
        $this->eaiTransactionId = new EaiTransactionId($this->requestStack->reveal());
        $this->eaiTransactionId->setTransactionIdOverride('overrided');
        $this->assertEquals(
            'overrided',
            $this->eaiTransactionId->getTransactionId()
        );
        $this->eaiTransactionId->resetTransactionIdOverride();
        $this->assertEquals(
            'original-eai-transaction-id',
            $this->eaiTransactionId->getTransactionId()
        );
    }
}
