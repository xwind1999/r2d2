<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Contract\Request\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\RoomPrice\RoomPriceUpdateRequest;

class RoomPriceApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/room-price';
    protected RoomPriceCreateRequest $roomPriceCreateRequest;

    public function setUp(): void
    {
        $this->roomPriceCreateRequest = new RoomPriceCreateRequest();
        $this->roomPriceCreateRequest->roomGoldenId = '1234';
        $this->roomPriceCreateRequest->rateBandGoldenId = '1123';
        $this->roomPriceCreateRequest->date = new \DateTime();
        $this->roomPriceCreateRequest->price = 99;
    }

    public function testCreateSuccess(): string
    {
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->roomPriceCreateRequest));
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
        $payload = new RoomPriceUpdateRequest();
        $payload->uuid = $uuid;
        $payload->roomGoldenId = '1234';
        $payload->rateBandGoldenId = '12';
        $payload->date = new \DateTime();
        $payload->price = 120;

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
