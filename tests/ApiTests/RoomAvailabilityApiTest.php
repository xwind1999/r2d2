<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;

class RoomAvailabilityApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/room-availability';
    protected RoomAvailabilityCreateRequest $roomAvailabilityCreateRequest;

    public function setUp(): void
    {
        $this->roomAvailabilityCreateRequest = new RoomAvailabilityCreateRequest();

        $this->roomAvailabilityCreateRequest->roomGoldenId = '1234';
        $this->roomAvailabilityCreateRequest->rateBandGoldenId = '5678';
        $this->roomAvailabilityCreateRequest->stock = 2;
        $this->roomAvailabilityCreateRequest->date = new \DateTime();
        $this->roomAvailabilityCreateRequest->type = 'instant';
    }

    public function testCreateWithInvalidRoomGoldenId()
    {
        $this->roomAvailabilityCreateRequest->roomGoldenId = '';
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->roomAvailabilityCreateRequest));
        $this->assertEquals(422, $this->client()->getResponse()->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->roomAvailabilityCreateRequest));
        $response = json_decode($this->client()->getResponse()->getContent(), true);
        $this->assertArrayHasKey('uuid', $response);
        $this->assertEquals(201, $this->client()->getResponse()->getStatusCode());

        return $response['uuid'];
    }

    /**
     * @depends testCreateSuccess
     */
    public function testGet(string $uuid): string
    {
        $this->client()->request('GET', sprintf('%s/%s', self::API_BASE_URL, $uuid), [], [], []);
        $response = json_decode($this->client()->getResponse()->getContent(), true);
        $this->assertArrayHasKey('uuid', $response);
        $this->assertArrayHasKey('created_at', $response);
        $this->assertEquals(200, $this->client()->getResponse()->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testGet
     */
    public function testUpdate(string $uuid): string
    {
        $payload = new RoomAvailabilityUpdateRequest();
        $payload->roomGoldenId = '9876';
        $payload->rateBandGoldenId = '5432';
        $payload->stock = 1;
        $payload->date = new \DateTime();
        $payload->type = 'request';
        $this->client()->request('PUT', sprintf('%s/%s', self::API_BASE_URL, $uuid), [], [], [], $this->serialize($payload));
        $response = json_decode($this->client()->getResponse()->getContent(), true);
        $this->assertNull($response);
        $this->assertEquals(204, $this->client()->getResponse()->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $this->client()->request('DELETE', sprintf('%s/%s', self::API_BASE_URL, $uuid), [], [], []);
        $response = json_decode($this->client()->getResponse()->getContent(), true);
        $this->assertNull($response);
        $this->assertEquals(204, $this->client()->getResponse()->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testDelete
     */
    public function testGetAfterDelete(string $uuid)
    {
        $this->client()->request('GET', sprintf('%s/%s', self::API_BASE_URL, $uuid), [], [], []);
        $this->assertEquals(404, $this->client()->getResponse()->getStatusCode());
    }
}
