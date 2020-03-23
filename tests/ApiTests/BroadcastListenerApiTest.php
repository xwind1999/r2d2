<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BroadcastListenerApiTest extends ApiTestCase
{
    const API_PRODUCT_BASE_URL = '/broadcast-listener/product';
    const API_PARTNER_BASE_URL = '/broadcast-listener/partner';

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
}
