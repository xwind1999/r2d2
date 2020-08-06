<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\ListPrice;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Event\Product\BoxBroadcastEvent;
use App\Exception\Resolver\UnprocessableProductTypeException;
use App\Handler\ProductBroadcastHandler;
use App\Resolver\ProductTypeResolver;
use phpDocumentor\Reflection\Types\Void_;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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
     */
    public function testHandlerMessageBoxType(): void
    {
        $country = Country::create('FR');
        $brand = Brand::create('SBX');
        $productRequest = new ProductRequest();
        $productRequest->id = '1234';
        $productRequest->sellableBrand = $brand;
        $productRequest->sellableCountry = $country;
        $productRequest->status = 'active';
        $productRequest->type = 'MEV';
        $productRequest->listPrice = new ListPrice();
        $productRequest->listPrice->currencyCode = 'EUR';

        $logger = $this->prophesize(LoggerInterface::class);
        $productTypeResolver = $this->prophesize(ProductTypeResolver::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $boxEvent = new BoxBroadcastEvent($productRequest);

        $productTypeResolver->resolve($productRequest)->shouldBeCalled()->willReturn($boxEvent);
        $eventDispatcher->dispatch($boxEvent)->shouldBeCalled()->willReturn($boxEvent);

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
     * @covers \App\Contract\Request\BroadcastListener\Product\ListPrice::getContext
     */
    public function testHandlerMessageThrowsNonExistentTypeResolverException(): void
    {
        $partner = Partner::create('4321');
        $universe = Universe::create('universe');
        $country = Country::create('FR');
        $brand = Brand::create('SBX');
        $productRequest = new ProductRequest();
        $productRequest->id = '1234';
        $productRequest->name = 'box name';
        $productRequest->description = 'description';
        $productRequest->universe = $universe;
        $productRequest->isSellable = true;
        $productRequest->isReservable = true;
        $productRequest->partner = $partner;
        $productRequest->sellableBrand = $brand;
        $productRequest->sellableCountry = $country;
        $productRequest->status = 'active';
        $productRequest->type = 'MEV';
        $productRequest->listPrice = new ListPrice();
        $productRequest->listPrice->currencyCode = 'EUR';
        $productRequest->listPrice->amount = 11;

        $logger = $this->prophesize(LoggerInterface::class);
        $productTypeResolver = $this->prophesize(ProductTypeResolver::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $productTypeResolver->resolve($productRequest)->shouldBeCalled()->willThrow(new UnprocessableProductTypeException());

        $productBroadcastHandler = new ProductBroadcastHandler(
            $logger->reveal(),
            $productTypeResolver->reveal(),
            $eventDispatcher->reveal())
        ;

        $logger->warning(Argument::any(), $productRequest->getContext())->shouldBeCalled()->willReturn(Void_::class);

        $this->assertEquals(null, $productBroadcastHandler->__invoke($productRequest));
    }

    /**
     * @covers \App\Contract\Request\BroadcastListener\ProductRequest::getEventName
     */
    public function testProductEventName(): void
    {
        $this->assertEquals('Product broadcast', (new ProductRequest())->getEventName());
    }
}
