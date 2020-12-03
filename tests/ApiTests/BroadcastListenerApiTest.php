<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BroadcastListenerApiTest extends ApiTestCase
{
    public function testHandleBoxProduct(): int
    {
        $response = self::$broadcastListenerHelper->testBoxProduct();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleExperienceProduct(): int
    {
        $response = self::$broadcastListenerHelper->testExperienceProduct();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleComponentProductAsStock(): int
    {
        $response = self::$broadcastListenerHelper->testComponentProduct();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleComponentProductAsOnRequest(): int
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'Test Component 1',
            'description' => 'Test Component Description 1',
            'isSellable' => true,
            'isReservable' => true,
            'partner' => [
                'id' => bin2hex(random_bytes(12)),
            ],
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'sellableCountry' => [
                'code' => 'FR',
            ],
            'status' => 'active',
            'type' => 'component',
            'productDuration' => 2,
            'productDurationUnit' => 'Nights',
            'roomStockType' => 'on_request',
            'listPrice' => [
                'currencyCode' => 'EUR',
                'amount' => 100,
            ],
        ];

        $response = self::$broadcastListenerHelper->testComponentProduct($payload);
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandlePartners(): int
    {
        $response = self::$broadcastListenerHelper->testPartners();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testErrorResponsePartnerWrongStatus(): int
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'status' => 'new',
            'currencyCode' => 'USD',
        ];

        $response = self::$broadcastListenerHelper->testPartners($payload);
        $this->assertEquals(422, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testErrorResponsePartnerWithNoCurrencyCode(): int
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'status' => 'new partner',
        ];

        $response = self::$broadcastListenerHelper->testPartners($payload);
        $this->assertEquals(422, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleBoxExperienceRelationship(): int
    {
        $response = self::$broadcastListenerHelper->testBoxExperienceRelationship();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleExperienceComponentRelationship(): int
    {
        $response = self::$broadcastListenerHelper->testExperienceComponentRelationship();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandlePriceInformation(): int
    {
        $response = self::$broadcastListenerHelper->testPriceInformation();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleRoomAvailability(): int
    {
        $response = self::$broadcastListenerHelper->testRoomAvailability();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function test400BadRequestRoomAvailabilityWrongDateFormat(): int
    {
        $payload = [
            [
                'product' => [
                    'id' => '315172',
                ],
                'quantity' => 2,
                'dateFrom' => '2020-07-16',
                'dateTo' => '2020-07-20T20:00:00.000000+0000',
                'updatedAt' => '2020-07-20T17:58:32.000000+0000',
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(400, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleRoomPrice(): int
    {
        $response = self::$broadcastListenerHelper->testRoomPrice();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function test400BadRequestRoomPriceWithWrongDateFormat(): int
    {
        $payload = [
            [
                'product' => [
                    'id' => '315618',
                ],
                'price' => [
                    'amount' => 20.00,
                    'currencyCode' => 'EUR',
                ],
                'dateFrom' => '2020-07-16',
                'dateTo' => '2020-07-20T20:00:00.000000+0000',
                'updatedAt' => '2020-07-20T17:58:32.000000+0000',
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomPrice($payload);
        $this->assertEquals(400, $response->getStatusCode());

        return $response->getStatusCode();
    }
}
