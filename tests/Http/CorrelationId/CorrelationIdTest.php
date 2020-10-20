<?php

declare(strict_types=1);

namespace App\Tests\Http\CorrelationId;

use App\Http\CorrelationId\CorrelationId;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \App\Http\CorrelationId\CorrelationId
 */
class CorrelationIdTest extends TestCase
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

    private CorrelationId $correlationId;

    public function setUp(): void
    {
        $this->requestStack = $this->prophesize(RequestStack::class);
        $this->request = $this->prophesize(Request::class);
        $this->headers = $this->prophesize(HeaderBag::class);
    }

    /**
     * @covers ::__construct
     * @covers ::getCorrelationId
     */
    public function testGetCorrelationIdWithHeaderCorrelationId(): void
    {
        $this->request->headers = $this->headers->reveal();
        $this->headers->get('Correlation-Id', null)->willReturn('correlation-id-is-1234567');
        $this->requestStack->getMasterRequest()->willReturn($this->request->reveal());
        $this->correlationId = new CorrelationId($this->requestStack->reveal());
        $this->assertEquals(
            'correlation-id-is-1234567',
            $this->correlationId->getCorrelationId()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getCorrelationId
     */
    public function testGetCorrelationIdWithoutRequestWillGenerateANewCorrelationId(): void
    {
        $this->requestStack->getMasterRequest()->willReturn(null);
        $this->correlationId = new CorrelationId($this->requestStack->reveal());
        $this->assertNotNull($this->correlationId->getCorrelationId());
    }

    /**
     * @covers ::__construct
     * @covers ::getCorrelationId
     */
    public function testGetCorrelationIdWithoutRequestWillGenerateANewCorrelationIdAndReuseIt(): void
    {
        $this->requestStack->getMasterRequest()->willReturn(null);
        $this->correlationId = new CorrelationId($this->requestStack->reveal());
        $correlationId = $this->correlationId->getCorrelationId();
        $this->assertEquals($correlationId, $this->correlationId->getCorrelationId());
    }

    /**
     * @covers ::__construct
     * @covers ::getCorrelationId
     */
    public function testGetCorrelationIdWithoutHeadersWillGenerateANewCorrelationId(): void
    {
        $this->request->headers = $this->headers->reveal();
        $this->headers->get('Correlation-Id', null)->willReturn(null);
        $this->requestStack->getMasterRequest()->willReturn($this->request->reveal());
        $this->correlationId = new CorrelationId($this->requestStack->reveal());
        $this->assertNotNull($this->correlationId->getCorrelationId());
    }

    /**
     * @covers ::__construct
     * @covers ::getCorrelationId
     * @covers ::resetCorrelationIdOverride
     * @covers ::setCorrelationIdOverride
     */
    public function testGetCorrelationIdWithOverride(): void
    {
        $this->request->headers = $this->headers->reveal();
        $this->headers->get('Correlation-Id', null)->willReturn('original-correlation-id');
        $this->requestStack->getMasterRequest()->willReturn($this->request->reveal());
        $this->correlationId = new CorrelationId($this->requestStack->reveal());
        $this->correlationId->setCorrelationIdOverride('overrided');
        $this->assertEquals(
            'overrided',
            $this->correlationId->getCorrelationId()
        );
        $this->correlationId->resetCorrelationIdOverride();
        $this->assertEquals(
            'original-correlation-id',
            $this->correlationId->getCorrelationId()
        );
    }
}
