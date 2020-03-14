<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class RateBandApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/rate-band';

    public function testCreateWithInvalidPartnerGoldenId()
    {
        $rateBandCreateRequest = self::$rateBandHelper->getDefault(['partner_golden_id' => '']);
        $response = self::$rateBandHelper->create($rateBandCreateRequest);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateWithNonExistentPartner()
    {
        $rateBandCreateRequest = self::$rateBandHelper->getDefault(['partner_golden_id' => 'non-existent-partner']);
        $response = self::$rateBandHelper->create($rateBandCreateRequest);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$rateBandHelper->create();
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
        $response = self::$rateBandHelper->get($uuid);
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
        $rateBand = json_decode(self::$rateBandHelper->get($uuid)->getContent(), true);
        $payload = [
            'name' => 'updated test rate band',
        ] + $rateBand;
        $response = self::$rateBandHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($payload['name'], $responseContent->name);
        $this->assertEquals(200, $response->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$rateBandHelper->delete($uuid);
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
        $response = self::$rateBandHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
