<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BookingDateApiTest extends ApiTestCase
{
    public function testCreateWithInvalidBookingGoldenId()
    {
        $payload = self::$boxHelper->getDefault(['booking_golden_id' => '']);
        $response = self::$bookingDateHelper->create($payload);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$bookingDateHelper->create();
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
        $response = self::$bookingDateHelper->get($uuid);
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
        $bookingDate = json_decode(self::$bookingDateHelper->get($uuid)->getContent(), true);
        $payload = [
                'price' => 10990,
            ] + $bookingDate;
        $response = self::$bookingDateHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($payload['price'], $responseContent->price);
        $this->assertEquals(200, $response->getStatusCode());

        return $responseContent->uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$bookingDateHelper->delete($uuid);
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
        $response = self::$bookingDateHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
