<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\BoxBroadcastEvent;
use App\Handler\ProductBroadcastHandler;
use App\Resolver\Exception\NonExistentTypeResolverExcepetion;
use App\Resolver\ProductTypeResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \App\Handler\ProductBroadcastHandler
 */
class ProductBroadcastHandlerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Event\Product\BoxBroadcastEvent::getEventName
     */
    public function testHandlerMessageBoxType(): void
    {
        $productRequest = new ProductRequest();
        $productRequest->uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $productRequest->goldenId = '1234';
        $productRequest->brand = 'SBX';
        $productRequest->country = 'FR';
        $productRequest->status = 'active';
        $productRequest->type = 'MEV';

        $logger = $this->prophesize(LoggerInterface::class);
        $productTypeResolver = $this->prophesize(ProductTypeResolver::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $boxEvent = new BoxBroadcastEvent($productRequest);

        $productTypeResolver->resolve($productRequest)->shouldBeCalled()->willReturn($boxEvent);
        $eventDispatcher->dispatch($boxEvent, $boxEvent->getEventName())->shouldBeCalled()->willReturn($boxEvent);

        $productBroadcastHandler = new ProductBroadcastHandler(
            $logger->reveal(),
            $productTypeResolver->reveal(),
            $eventDispatcher->reveal())
        ;

        $this->assertEquals(null, $productBroadcastHandler->__invoke($productRequest));
    }

    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::getContext
     */
    public function testHandlerMessageThrowsNonExistentTypeResolverException(): void
    {
        $productRequest = new ProductRequest();
        $productRequest->uuid = 'eedc7cbe-5328-11ea-8d77-2e728ce88125';
        $productRequest->goldenId = '1234';
        $productRequest->name = 'box name';
        $productRequest->description = 'description';
        $productRequest->universe = 'description';
        $productRequest->isSellable = true;
        $productRequest->isReservable = true;
        $productRequest->partnerGoldenId = '4321';
        $productRequest->brand = 'SBX';
        $productRequest->country = 'FR';
        $productRequest->status = 'active';
        $productRequest->type = 'MEV';

        $logger = $this->prophesize(LoggerInterface::class);
        $productTypeResolver = $this->prophesize(ProductTypeResolver::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $productTypeResolver->resolve($productRequest)->shouldBeCalled()->willThrow(new NonExistentTypeResolverExcepetion());

        $productBroadcastHandler = new ProductBroadcastHandler(
            $logger->reveal(),
            $productTypeResolver->reveal(),
            $eventDispatcher->reveal())
        ;

        $logger->warning('', $productRequest->getContext())->shouldBeCalled()->willReturn(Void_::class);

        $this->assertEquals(null, $productBroadcastHandler->__invoke($productRequest));
    }
}
