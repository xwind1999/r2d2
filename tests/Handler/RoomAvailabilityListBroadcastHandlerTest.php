<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequestList;
use App\Exception\Repository\ComponentNotFoundException;
use App\Handler\RoomAvailabilityListBroadcastHandler;
use App\Manager\RoomAvailabilityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\RoomAvailabilityListBroadcastHandler
 */
class RoomAvailabilityListBroadcastHandlerTest extends TestCase
{
    /**
     * @dataProvider roomAvailabilityRequestListProvider
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandleBroadcastRoomAvailability(
        RoomAvailabilityRequestList $roomAvailabilityRequestList,
        ?\Throwable $exception = null
    ): void {
        $manager = $this->prophesize(RoomAvailabilityManager::class);
        $manager->dispatchRoomAvailabilitiesRequest(Argument::any())->shouldBeCalled();
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new RoomAvailabilityListBroadcastHandler($logger->reveal(), $manager->reveal());

        if ($exception) {
            $this->expectException(get_class($exception));
            $manager->dispatchRoomAvailabilitiesRequest(Argument::any())->willThrow($exception);
            $logger->warning($exception, $roomAvailabilityRequestList->getContext())->shouldBeCalled();
        }

        $this->assertNull($handler($roomAvailabilityRequestList));
    }

    /**
     * @see testHandleBroadcastRoomAvailability
     */
    public function roomAvailabilityRequestListProvider(): ?\Generator
    {
        $roomAvailabilityRequestList = new RoomAvailabilityRequestList();
        $roomAvailabilityRequest = new RoomAvailabilityRequest();
        $product = new Product();
        $product->id = '315172';
        $roomAvailabilityRequest->product = $product;
        $roomAvailabilityRequest->dateFrom = new \DateTime('+9 days');
        $roomAvailabilityRequest->dateTo = new \DateTime('+13 days');
        $roomAvailabilityRequest->quantity = 3;
        $roomAvailabilityRequest->updatedAt = new \DateTime('-3 days');
        $roomAvailabilityRequestList->items = [$roomAvailabilityRequest];

        yield 'broadcast-happy-days' => [
            clone $roomAvailabilityRequestList,
        ];

        yield 'without-stock' => [
            (function ($roomAvailabilityRequestList) {
                $roomAvailabilityRequestList->items[0]->product->stock = 0;

                return $roomAvailabilityRequestList;
            })(clone $roomAvailabilityRequestList),
        ];

        yield 'product-id-not-found' => [
            (function ($roomAvailabilityRequestList) {
                $roomAvailabilityRequestList->items[0]->product->id = '123456789';

                return $roomAvailabilityRequestList;
            })(clone $roomAvailabilityRequestList),
            new ComponentNotFoundException(),
        ];
    }
}
