<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class RoomAvailabilityApiTest extends ApiTestCase
{
    public function testCreateWithInvalidRoomGoldenId()
    {
        $response = self::$roomAvailabilityHelper->create(['component_golden_id' => '']);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$roomAvailabilityHelper->create();
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
        $response = self::$roomAvailabilityHelper->get($uuid);
        $responseContent = json_decode($response->getContent());
        $this->assertObjectHasAttribute('uuid', $responseContent);
        $this->assertObjectHasAttribute('created_at', $responseContent);
        $this->assertEquals(200, $response->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testGet
     */
    public function testUpdate(string $uuid): string
    {
        $roomPrice = json_decode(self::$roomAvailabilityHelper->get($uuid)->getContent(), true);
        $payload = [
                'stock' => 51,
            ] + $roomPrice;
        $response = self::$roomAvailabilityHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($payload['stock'], $responseContent->stock);
        $this->assertEquals(200, $response->getStatusCode());

        return $responseContent->uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$roomAvailabilityHelper->delete($uuid);
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
        $response = self::$roomAvailabilityHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
