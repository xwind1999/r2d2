<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Contract\Request\Room\RoomCreateRequest;
use App\Contract\Request\Room\RoomUpdateRequest;

class RoomApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/room';
    protected RoomCreateRequest $roomCreateRequest;

    public function setUp(): void
    {
        $this->roomCreateRequest = new RoomCreateRequest();
        $this->roomCreateRequest->goldenId = '1234';
        $this->roomCreateRequest->partnerGoldenId = '5678';
        $this->roomCreateRequest->name = 'test room';
        $this->roomCreateRequest->description = 'this is a test room';
        $this->roomCreateRequest->inventory = 1;
        $this->roomCreateRequest->isSellable = true;
        $this->roomCreateRequest->status = 'live';
    }

    public function testCreateWithInvalidPartnerGoldenId()
    {
        $this->roomCreateRequest->partnerGoldenId = '';
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->roomCreateRequest));
        $this->assertEquals(422, $this->client()->getResponse()->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->roomCreateRequest));
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
        $payload = new RoomUpdateRequest();
        $payload->uuid = $uuid;
        $payload->goldenId = '9898';
        $payload->partnerGoldenId = '2222';
        $payload->name = 'updated test room';
        $payload->description = 'this is a test room, but updated';
        $payload->inventory = 2;
        $payload->isSellable = false;
        $payload->status = 'gone';
        $this->client()->request('PUT', self::API_BASE_URL, [], [], [], $this->serialize($payload));
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
