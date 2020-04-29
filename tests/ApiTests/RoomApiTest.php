<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class RoomApiTest extends ApiTestCase
{
    public function testCreateWithInvalidPartnerGoldenId()
    {
        $roomCreateRequest = self::$roomHelper->getDefault(['partner_golden_id' => '']);
        $response = self::$roomHelper->create($roomCreateRequest);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateWithNonExistentPartner()
    {
        $roomCreateRequest = self::$roomHelper->getDefault(['partner_golden_id' => 'non-existent-partner']);
        $response = self::$roomHelper->create($roomCreateRequest);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$roomHelper->create();
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
        $response = self::$roomHelper->get($uuid);
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
        $room = json_decode(self::$roomHelper->get($uuid)->getContent(), true);
        $payload = [
            'name' => 'updated test room',
            'description' => 'this is a test room, but updated',
        ] + $room;
        $response = self::$roomHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($payload['name'], $responseContent->name);
        $this->assertEquals(200, $response->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$roomHelper->delete($uuid);
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
        $response = self::$roomHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
