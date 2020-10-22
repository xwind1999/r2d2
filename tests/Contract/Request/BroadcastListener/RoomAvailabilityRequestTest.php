<?php

declare(strict_types=1);

namespace App\Tests\Contract\Request\BroadcastListener;

use App\Contract\Request\BroadcastListener\Product\Product;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Contract\Request\BroadcastListener\RoomAvailabilityRequest
 * @group room-availability-request
 */
class RoomAvailabilityRequestTest extends ProphecyTestCase
{
    /**
     * @dataProvider roomAvailabilityRequestProvider
     * @covers ::getContext
     */
    public function testGetContext($requestList, ?callable $asserts = null, ?string $exceptionType = null)
    {
        if ($exceptionType) {
            $this->expectException($exceptionType);
        }

        foreach ($requestList as $request) {
            $roomAvailabilityRequest = new RoomAvailabilityRequest();
            $roomAvailabilityRequest->product = $request['product'];
            $roomAvailabilityRequest->quantity = $request['quantity'];
            $roomAvailabilityRequest->dateFrom = $request['dateFrom'];
            $roomAvailabilityRequest->dateTo = $request['dateTo'];
            $roomAvailabilityRequest->isStopSale = $request['isStopSale'];
            $roomAvailabilityRequest->updatedAt = $request['updatedAt'];
            $roomAvailabilityRequest = $roomAvailabilityRequest->getContext();
        }

        if ($asserts) {
            $asserts($this, $request, $roomAvailabilityRequest);
        }
    }

    public function roomAvailabilityRequestProvider(): \Generator
    {
        $product = new Product();
        $product->id = '218439';

        $requestList = [
            [
                'product' => $product,
                'quantity' => 2,
                'isStopSale' => true,
                'dateFrom' => new \DateTime('+2 days'),
                'dateTo' => new \DateTime('+5 days'),
                'updatedAt' => new \DateTime('-2 days'),
            ],
        ];

        yield 'happy-days' => [
            $requestList,
            (function (RoomAvailabilityRequestTest $test, array $request, array $roomAvailabilityRequest) {
                $test->assertEquals($request['product']->id, $roomAvailabilityRequest['product']['id']);
                $test->assertEquals($request['dateFrom'], $roomAvailabilityRequest['dateFrom']);
                $test->assertEquals($request['dateTo'], $roomAvailabilityRequest['dateTo']);
                $test->assertEquals($request['quantity'], $roomAvailabilityRequest['quantity']);
                $test->assertEquals($request['updatedAt']->format('Y-m-d H:i:s'), $roomAvailabilityRequest['updatedAt']);
            }),
        ];

        yield 'multiple-requests' => [
            (function ($requestList) {
                $product = new Product();
                $product->id = '9999';
                $requestList[] =
                [
                    'product' => $product,
                    'quantity' => 3,
                    'dateFrom' => (new \DateTime('+5 days'))->format('Y-m-d'),
                    'dateTo' => (new \DateTime('+7 days'))->format('Y-m-d'),
                    'updatedAt' => new \DateTime('-3 days'),
                ];

                return $requestList;
            })($requestList),
            null,
            \TypeError::class,
        ];

        yield 'updated-at-as-null' => [
            (function ($requestList) {
                $requestList[0]['updatedAt'] = null;

                return $requestList;
            })($requestList),
            (function (RoomAvailabilityRequestTest $test, array $request, array $roomAvailabilityRequest) {
                $test->assertEquals($request['product']->id, $roomAvailabilityRequest['product']['id']);
                $test->assertEquals($request['dateFrom'], $roomAvailabilityRequest['dateFrom']);
                $test->assertEquals($request['dateTo'], $roomAvailabilityRequest['dateTo']);
                $test->assertEquals($request['quantity'], $roomAvailabilityRequest['quantity']);
                $test->assertEquals($request['updatedAt'], $roomAvailabilityRequest['updatedAt']);
            }),
        ];

        yield 'updated-at-as-string' => [
            (function ($requestList) {
                $requestList[0]['updatedAt'] = (new \DateTime('yesterday'))->format('Y-m-d H:i:s');

                return $requestList;
            })($requestList),
            null,
            \TypeError::class,
        ];
    }

    public function testGetEventName()
    {
        $request = new RoomAvailabilityRequest();
        $this->assertEquals('Room availability broadcast', $request->getEventName());
    }
}
