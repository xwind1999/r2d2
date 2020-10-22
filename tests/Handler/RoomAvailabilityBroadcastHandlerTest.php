<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Exception\Repository\ComponentNotFoundException;
use App\Handler\RoomAvailabilityBroadcastHandler;
use App\Manager\RoomAvailabilityManager;
use App\Tests\ProphecyTestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\RoomAvailabilityBroadcastHandler
 */
class RoomAvailabilityBroadcastHandlerTest extends ProphecyTestCase
{
    /**
     * @dataProvider roomAvailabilityRequestProvider
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testHandleBroadcastRoomAvailability(
        RoomAvailabilityRequest $roomAvailabilityRequest,
        ?\Throwable $exception = null)
    {
        $manager = $this->prophesize(RoomAvailabilityManager::class);
        $manager->replace(Argument::any())->shouldBeCalled();
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new RoomAvailabilityBroadcastHandler($logger->reveal(), $manager->reveal());

        if ($exception) {
            $this->expectException(get_class($exception));
            $manager->replace(Argument::any())->willThrow($exception);
            $logger->warning($exception, $roomAvailabilityRequest->getContext())->shouldBeCalled();
        }

        $this->assertNull($handler($roomAvailabilityRequest));
    }

    public function roomAvailabilityRequestProvider()
    {
        $roomAvailabilityRequest = new RoomAvailabilityRequest();
        $product = new Product();
        $product->id = '315172';
        $roomAvailabilityRequest->product = $product;
        $roomAvailabilityRequest->dateFrom = new \DateTime('+9 days');
        $roomAvailabilityRequest->dateTo = new \DateTime('+13 days');
        $roomAvailabilityRequest->quantity = 3;
        $roomAvailabilityRequest->updatedAt = new \DateTime('-3 days');

        yield 'broadcast-happy-days' => [
            clone $roomAvailabilityRequest,
        ];

        yield 'without-stock' => [
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->stock = 0;

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
        ];

        yield 'product-id-not-found' => [
            (function ($roomAvailabilityRequest) {
                $roomAvailabilityRequest->product->id = '123456789';

                return $roomAvailabilityRequest;
            })(clone $roomAvailabilityRequest),
            new ComponentNotFoundException(),
        ];
    }
}
