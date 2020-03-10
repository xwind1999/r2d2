<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Contract\Request\Partner\PartnerCreateRequest;
use App\Contract\Request\Partner\PartnerUpdateRequest;

class PartnerApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/partner';
    protected PartnerCreateRequest $partnerCreateRequest;

    public function setUp(): void
    {
        $this->partnerCreateRequest = new PartnerCreateRequest();
        $this->partnerCreateRequest->goldenId = '1234';
        $this->partnerCreateRequest->status = 'active';
        $this->partnerCreateRequest->currency = 'EUR';
    }

    public function testCreateWithInvalidGoldenId()
    {
        $this->partnerCreateRequest->goldenId = '';
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->partnerCreateRequest));
        $this->assertEquals(422, $this->client()->getResponse()->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->partnerCreateRequest));
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
        $payload = new PartnerUpdateRequest();
        $payload->uuid = $uuid;
        $payload->goldenId = '9999';
        $payload->status = 'inactive';
        $payload->currency = 'DKK';
        $payload->ceaseDate = new \DateTime();
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
