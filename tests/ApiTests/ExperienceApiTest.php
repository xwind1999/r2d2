<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Contract\Request\Experience\ExperienceCreateRequest;
use App\Contract\Request\Experience\ExperienceUpdateRequest;

class ExperienceApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/experience';
    protected ExperienceCreateRequest $experienceCreateRequest;

    public function setUp(): void
    {
        $this->experienceCreateRequest = new ExperienceCreateRequest();
        $this->experienceCreateRequest->goldenId = '1234';
        $this->experienceCreateRequest->partnerGoldenId = '1234';
        $this->experienceCreateRequest->name = '1234';
        $this->experienceCreateRequest->description = '1234';
        $this->experienceCreateRequest->duration = 1;
    }

    public function testCreateWithInvalidPartnerGoldenId()
    {
        $this->experienceCreateRequest->partnerGoldenId = '';
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->experienceCreateRequest));
        $this->assertEquals(422, $this->client()->getResponse()->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->experienceCreateRequest));
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
        $payload = new ExperienceUpdateRequest();
        $payload->uuid = $uuid;
        $payload->goldenId = '1234';
        $payload->partnerGoldenId = '5678';
        $payload->name = '98767';
        $payload->description = '1234';
        $payload->duration = 2;
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
