<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BroadcastListenerApiTest extends ApiTestCase
{
    public function testHandleProducts(): int
    {
        $response = self::$broadcastListenerHelper->testProducts();
        print_r($response->getContent());
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandlePartners(): int
    {
        $response = self::$broadcastListenerHelper->testPartners();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleRelationships(): int
    {
        $response = self::$broadcastListenerHelper->testRelationships();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandlePriceInformation(): int
    {
        $response = self::$broadcastListenerHelper->testPriceInformations();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleRoomAvailability(): int
    {
        $response = self::$broadcastListenerHelper->testRoomAvailability();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleRoomPrice(): int
    {
        $response = self::$broadcastListenerHelper->testRoomPrice();
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }
}
