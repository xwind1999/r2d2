<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Contract\Request\BroadcastListener\RoomPriceRequestList;
use App\Exception\Repository\ComponentNotFoundException;
use App\Handler\RoomPriceListBroadcastHandler;
use App\Manager\RoomPriceManager;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\RoomPriceListBroadcastHandler
 */
class RoomPriceListBroadcastHandlerTest extends ProphecyTestCase
{
    /**
     * @dataProvider roomPriceRequestProvider
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\BroadcastListener\RoomPriceRequest::getContext
     */
    public function testHandleBroadcastRoomPrice(
        RoomPriceRequestList $roomPriceRequestList,
        ?\Throwable $exception = null
    ): void {
        $manager = $this->prophesize(RoomPriceManager::class);
        $manager->dispatchRoomPricesRequest(Argument::any())->shouldBeCalled();
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new RoomPriceListBroadcastHandler($logger->reveal(), $manager->reveal());

        if ($exception) {
            $this->expectException(get_class($exception));
            $manager->dispatchRoomPricesRequest(Argument::any())->willThrow($exception);
            $logger->warning($exception, $roomPriceRequestList->getContext())->shouldBeCalled();
        }

        $this->assertNull($handler($roomPriceRequestList));
    }

    public function roomPriceRequestProvider(): ?\Generator
    {
        $roomPriceRequestList = new RoomPriceRequestList();
        $roomPriceRequest = new RoomPriceRequest();
        $product = new Product();
        $product->id = '315172';
        $roomPriceRequest->product = $product;
        $roomPriceRequest->dateFrom = new \DateTime('+9 days');
        $roomPriceRequest->dateTo = new \DateTime('+13 days');
        $roomPriceRequest->price = new Price();
        $roomPriceRequest->price->amount = 123;
        $roomPriceRequest->price->currencyCode = 'EUR';
        $roomPriceRequest->updatedAt = new \DateTime('-3 days');
        $roomPriceRequestList->items = [$roomPriceRequest];

        yield 'broadcast-happy-days' => [
            clone $roomPriceRequestList,
        ];

        yield 'product-id-not-found' => [
            (function ($roomPriceRequestList) {
                $roomPriceRequestList->items[0]->product->id = '123456789';

                return $roomPriceRequestList;
            })(clone $roomPriceRequestList),
            new ComponentNotFoundException(),
        ];
    }
}
