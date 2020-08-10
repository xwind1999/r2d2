<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Exception\Repository\ComponentNotFoundException;
use App\Handler\RoomPriceBroadcastHandler;
use App\Manager\RoomPriceManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Handler\RoomPriceBroadcastHandler
 */
class RoomPriceBroadcastHandlerTest extends TestCase
{
    /**
     * @dataProvider roomPriceRequestProvider
     * @covers ::__construct
     * @covers ::__invoke
     * @covers \App\Contract\Request\BroadcastListener\RoomPriceRequest::getContext
     */
    public function testHandleBroadcastRoomPrice(
        RoomPriceRequest $roomPriceRequest,
        ?\Throwable $exception = null)
    {
        $manager = $this->prophesize(RoomPriceManager::class);
        $manager->replace(Argument::any())->shouldBeCalled();
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new RoomPriceBroadcastHandler($logger->reveal(), $manager->reveal());

        if ($exception) {
            $this->expectException(get_class($exception));
            $manager->replace(Argument::any())->willThrow($exception);
            $logger->warning($exception, $roomPriceRequest->getContext())->shouldBeCalled();
        }

        $this->assertNull($handler($roomPriceRequest));
    }

    public function roomPriceRequestProvider()
    {
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

        yield 'broadcast-happy-days' => [
            clone $roomPriceRequest,
        ];

        yield 'product-id-not-found' => [
            (function ($roomPriceRequest) {
                $roomPriceRequest->product->id = '123456789';

                return $roomPriceRequest;
            })(clone $roomPriceRequest),
            new ComponentNotFoundException(),
        ];
    }
}
