<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BookingApiTest extends ApiTestCase
{
    public function testCreateWithInvalidBrand()
    {
        $response = self::$bookingHelper->create(['brand' => 'fr']);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$bookingHelper->create();
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
        $response = self::$bookingHelper->get($uuid);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertObjectHasAttribute('uuid', $responseContent);
        $this->assertObjectHasAttribute('created_at', $responseContent);

        return $uuid;
    }

    /**
     * @depends testGet
     */
    public function testUpdate(string $uuid): string
    {
        $bookingDate = json_decode(self::$bookingHelper->get($uuid)->getContent(), true);
        $payload = [
                'cancelled_at' => '2020-01-02T00:00:00+00:00',
            ] + $bookingDate;
        $response = self::$bookingHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($payload['cancelled_at'], $responseContent->cancelled_at);

        return $responseContent->uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$bookingHelper->delete($uuid);
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
        $response = self::$bookingHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
