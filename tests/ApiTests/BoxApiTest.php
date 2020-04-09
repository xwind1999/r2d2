<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BoxApiTest extends ApiTestCase
{
    protected const API_BASE_URL = '/api/box';

    public function testCreateWithExistentGoldenId()
    {
        $payload = self::$boxHelper->getDefault();
        self::$boxHelper->create($payload);
        $response = self::$boxHelper->create($payload);
        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testCreateWithInvalidBrand()
    {
        $payload = self::$boxHelper->getDefault(['brand' => 'does not exist']);
        $response = self::$boxHelper->create($payload);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$boxHelper->create();
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
        $response = self::$boxHelper->get($uuid);
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
        $box = json_decode(self::$boxHelper->get($uuid)->getContent(), true);
        $payload = [
            'brand' => 'bon',
        ] + $box;
        $response = self::$boxHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($payload['brand'], $responseContent->brand);
        $this->assertEquals(200, $response->getStatusCode());

        return $responseContent->uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$boxHelper->delete($uuid);
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
        $response = self::$boxHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
