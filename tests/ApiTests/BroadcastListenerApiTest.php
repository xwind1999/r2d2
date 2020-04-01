<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BroadcastListenerApiTest extends ApiTestCase
{
    public function testHandleProducts(): int
    {
        $response = self::$broadcastListenerHelper->testProducts();
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
}
