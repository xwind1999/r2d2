<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BroadcastListenerApiTest extends ApiTestCase
{
    public function testHandleBoxProduct(): int
    {
        $response = self::$broadcastListenerHelper->testBoxProduct();
        print_r($response->getContent());
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleExperienceProduct(): int
    {
        $response = self::$broadcastListenerHelper->testExperienceProduct();
        print_r($response->getContent());
        $this->assertEquals(202, $response->getStatusCode());

        return $response->getStatusCode();
    }

    public function testHandleComponentProduct(): int
    {
        $response = self::$broadcastListenerHelper->testComponentProduct();
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
