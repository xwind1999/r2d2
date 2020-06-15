<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class RoomPriceApiTest extends ApiTestCase
{
    public function testCreateSuccess(): string
    {
        $response = self::$roomPriceHelper->create();
        $responseContent = json_decode($response->getContent());
        $this->assertObjectHasAttribute('uuid', $responseContent);
        $this->assertEquals(201, $response->getStatusCode());

        return $responseContent->uuid;
    }

    /**
     * @depends testCreateSuccess
     */
    public function testGet(string $uuid): string
    {
        $response = self::$roomPriceHelper->get($uuid);
        $responseContent = json_decode($response->getContent());
        $this->assertObjectHasAttribute('uuid', $responseContent);
        $this->assertObjectHasAttribute('createdAt', $responseContent);
        $this->assertEquals(200, $response->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testGet
     */
    public function testUpdate(string $uuid): string
    {
        $roomPrice = json_decode(self::$roomPriceHelper->get($uuid)->getContent(), true);
        $payload = [
            'price' => 10990,
        ] + $roomPrice;
        $response = self::$roomPriceHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($payload['price'], $responseContent->price);
        $this->assertEquals(200, $response->getStatusCode());

        return $responseContent->uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$roomPriceHelper->delete($uuid);
        $responseContent = json_decode($response->getContent());
        $this->assertNull($responseContent);
        $this->assertEquals(204, $response->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testDelete
     */
    public function testGetAfterDelete(string $uuid)
    {
        $response = self::$roomPriceHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
