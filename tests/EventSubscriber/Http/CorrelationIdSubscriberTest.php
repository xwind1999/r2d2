<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber\Http;

use App\EventSubscriber\Http\CorrelationIdSubscriber;
use App\Http\CorrelationId\CorrelationId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @coversDefaultClass \App\EventSubscriber\Http\CorrelationIdSubscriber
 * @covers \App\Http\CorrelationId\CorrelationId
 * @group correlation-id
 */
class CorrelationIdSubscriberTest extends TestCase
{
    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                KernelEvents::RESPONSE => [
                    ['onKernelResponse', 255],
                ],
            ],
            CorrelationIdSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider responseProvider
     * @covers ::__construct
     * @covers ::onKernelResponse
     */
    public function testOnKernelResponse(ResponseEvent $responseEvent)
    {
        $requestStack = $this->prophesize(RequestStack::class);
        $correlationId = new CorrelationId($requestStack->reveal());
        $subscriber = new CorrelationIdSubscriber($correlationId);

        $headersResponse = $responseEvent->getResponse()->headers;

        $this->assertArrayNotHasKey('correlation-id', $headersResponse->all());

        $subscriber->onKernelResponse($responseEvent);

        $this->assertArrayHasKey(
            'correlation-id',
            $responseEvent->getResponse()->headers->all(),
            'The header must contain correlation-id'
        );
        $this->assertRegExp('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/',
            $responseEvent->getResponse()->headers->get('correlation-id'));
    }

    public function responseProvider()
    {
        $httpKernelInterface = $this->prophesize(HttpKernelInterface::class);
        $request = $this->prophesize(Request::class);
        $response = $this->prophesize(Response::class);

        $responseEvent = new ResponseEvent(
            $httpKernelInterface->reveal(),
            $request->reveal(),
            HttpKernelInterface::MASTER_REQUEST, $response->reveal()
        );

        $headers = new HeaderBag([
            'accept' => ['application/json'],
            'content-type' => ['application/json'],
        ]);

        yield 'responseWithoutCorrelationId' => [
            (function ($responseEvent, $headers) {
                $responseEvent->getResponse()->headers = $headers;

                return $responseEvent;
            })($responseEvent, $headers),
        ];
    }
}
