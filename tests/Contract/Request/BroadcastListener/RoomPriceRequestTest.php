<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\BroadcastListener;

use App\Constants\DateTimeConstants;
use App\Contract\Request\BroadcastListener\Common\Price;
use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Contract\Request\BroadcastListener\RoomPriceRequest
 */
class RoomPriceRequestTest extends ProphecyTestCase
{
    /**
     * @covers ::getContext
     */
    public function testGetContext(): void
    {
        $dateFrom = new \DateTime('yesterday');
        $dateTo = new \DateTime('tomorrow');
        $updatedAt = new \DateTime('today');
        $expected = [
            'product' => [
                'id' => '12345',
            ],
            'dateFrom' => $dateFrom->format(DateTimeConstants::DEFAULT_DATE_TIME_FORMAT),
            'dateTo' => $dateTo->format(DateTimeConstants::DEFAULT_DATE_TIME_FORMAT),
            'updatedAt' => $updatedAt->format(DateTimeConstants::DEFAULT_DATE_TIME_FORMAT),
            'price' => [
                'amount' => 150000,
                'currency_code' => 'EUR',
            ],
        ];

        $request = new RoomPriceRequest();
        $request->dateFrom = $dateFrom;
        $request->dateTo = $dateTo;
        $request->updatedAt = $updatedAt;

        $product = new Product();
        $product->id = '12345';
        $request->product = $product;

        $price = new Price();
        $price->amount = 150000;
        $price->currencyCode = 'EUR';
        $request->price = $price;

        $this->assertEquals($expected, $request->getContext());
    }

    /**
     * @covers ::getEventName
     */
    public function testGetEventName(): void
    {
        $request = new RoomPriceRequest();
        $this->assertEquals('Room price broadcast', $request->getEventName());
    }
}
