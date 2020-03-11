<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Contract\Request\BookingDate\BookingDateCreateRequest;
use App\Contract\Request\BookingDate\BookingDateUpdateRequest;

class BookingDateApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/booking-date';
    protected BookingDateCreateRequest $bookingDateCreateRequest;

    public function setUp(): void
    {
        $this->bookingDateCreateRequest = new BookingDateCreateRequest();
        $this->bookingDateCreateRequest->bookingGoldenId = '1234';
        $this->bookingDateCreateRequest->roomGoldenId = '1123';
        $this->bookingDateCreateRequest->rateBandGoldenId = '12';
        $this->bookingDateCreateRequest->date = new \DateTime();
        $this->bookingDateCreateRequest->price = 99;
        $this->bookingDateCreateRequest->isUpsell = true;
        $this->bookingDateCreateRequest->guestsCount = 1;
    }

    public function testCreateSuccess(): string
    {
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->bookingDateCreateRequest));
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
        $payload = new BookingDateUpdateRequest();
        $payload->uuid = $uuid;
        $payload->guestsCount = 2;
        $payload->bookingGoldenId = '1234';
        $payload->rateBandGoldenId = '12';
        $payload->isUpsell = true;
        $payload->roomGoldenId = '11234';
        $payload->price = 120;
        $payload->date = new \DateTime();

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
