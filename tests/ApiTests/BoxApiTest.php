<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Contract\Request\Box\BoxCreateRequest;
use App\Contract\Request\Box\BoxUpdateRequest;

class BoxApiTest extends ApiTestCase
{
    protected const API_BASE_URL = '/api/box';

    protected BoxCreateRequest $boxCreateRequest;

    public function setUp(): void
    {
        $this->boxCreateRequest = new BoxCreateRequest();
        $this->boxCreateRequest->goldenId = '1234';
        $this->boxCreateRequest->brand = 'sbx';
        $this->boxCreateRequest->country = 'fr';
        $this->boxCreateRequest->status = 'created';
    }

    public function testCreateWithInvalidBrand()
    {
        $this->boxCreateRequest->brand = 'fr';
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->boxCreateRequest));
        $this->assertEquals(422, $this->client()->getResponse()->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->boxCreateRequest));
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
        $payload = new BoxUpdateRequest();
        $payload->uuid = $uuid;
        $payload->goldenId = '1234';
        $payload->brand = 'sbx';
        $payload->country = 'fr';
        $payload->status = 'created';

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
