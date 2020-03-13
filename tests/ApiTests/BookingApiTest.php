<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;

class BookingApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/booking';
    protected BookingCreateRequest $bookingCreateRequest;

    public function setUp(): void
    {
        $this->bookingCreateRequest = new BookingCreateRequest();
        $this->bookingCreateRequest->goldenId = '1234';
        $this->bookingCreateRequest->partnerGoldenId = '5678';
        $this->bookingCreateRequest->experienceGoldenId = '9012';
        $this->bookingCreateRequest->type = 'booking';
        $this->bookingCreateRequest->voucher = '123456789';
        $this->bookingCreateRequest->brand = 'sbx';
        $this->bookingCreateRequest->country = 'fr';
        $this->bookingCreateRequest->requestType = 'instant';
        $this->bookingCreateRequest->channel = 'web';
        $this->bookingCreateRequest->cancellationChannel = null;
        $this->bookingCreateRequest->status = 'complete';
        $this->bookingCreateRequest->totalPrice = 150;
        $this->bookingCreateRequest->startDate = new \DateTime('2020-05-05');
        $this->bookingCreateRequest->endDate = new \DateTime('2020-05-06');
        $this->bookingCreateRequest->customerExternalId = 'W123123';
        $this->bookingCreateRequest->customerFirstName = null;
        $this->bookingCreateRequest->customerLastName = null;
        $this->bookingCreateRequest->customerEmail = null;
        $this->bookingCreateRequest->customerPhone = null;
        $this->bookingCreateRequest->customerComment = null;
        $this->bookingCreateRequest->partnerComment = null;
        $this->bookingCreateRequest->placedAt = new \DateTime();
        $this->bookingCreateRequest->cancelledAt = null;
    }

    public function testCreateWithInvalidBrand()
    {
        $this->bookingCreateRequest->brand = 'fr';
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->bookingCreateRequest));
        $this->assertEquals(422, $this->client()->getResponse()->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $this->client()->request('POST', self::API_BASE_URL, [], [], [], $this->serialize($this->bookingCreateRequest));
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
        $payload = new BookingUpdateRequest();
        $currentDate = new \DateTime();
        $payload->goldenId = '9876';
        $payload->partnerGoldenId = '5432';
        $payload->experienceGoldenId = '9012';
        $payload->type = 'booking';
        $payload->voucher = '123456789';
        $payload->brand = 'sbx';
        $payload->country = 'fr';
        $payload->requestType = 'instant';
        $payload->channel = 'web';
        $payload->cancellationChannel = null;
        $payload->status = 'complete';
        $payload->totalPrice = 150;
        $payload->startDate = new \DateTime('2020-05-05');
        $payload->endDate = new \DateTime('2020-05-06');
        $payload->customerExternalId = 'W123123';
        $payload->customerFirstName = 'new name';
        $payload->customerLastName = 'new last name';
        $payload->customerEmail = 'a@a.com';
        $payload->customerPhone = '55566666';
        $payload->customerComment = 'i want a double bed';
        $payload->partnerComment = 'i dont have double bed';
        $payload->placedAt = $currentDate;
        $payload->cancelledAt = $currentDate;

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
