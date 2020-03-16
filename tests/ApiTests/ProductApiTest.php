<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class ProductApiTest extends ApiTestCase
{
    const API_BASE_URL = '/broadcast-listeners/products';

    public function testCreateSuccess(): string
    {
        $response = self::$productHelper->create();
        $responseContent = json_decode($response->getContent());
        $this->assertEquals(201, $response->getStatusCode());

        return $responseContent->uuid;
    }

    public function testUpdate(): string
    {
        $response = self::$productHelper->update();
        $this->assertEquals(202, $response->getStatusCode());

        return '';
    }
}
