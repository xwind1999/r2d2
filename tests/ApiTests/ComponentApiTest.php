<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class ComponentApiTest extends ApiTestCase
{
    public function testCreateWithInvalidPartnerGoldenId()
    {
        $componentCreateRequest = self::$componentHelper->getDefault(['partner_golden_id' => '']);
        $response = self::$componentHelper->create($componentCreateRequest);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateWithNonExistentPartner()
    {
        $componentCreateRequest = self::$componentHelper->getDefault(['partner_golden_id' => 'non-existent-partner']);
        $response = self::$componentHelper->create($componentCreateRequest);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$componentHelper->create();
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
        $response = self::$componentHelper->get($uuid);
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
        $component = json_decode(self::$componentHelper->get($uuid)->getContent(), true);
        $payload = [
            'name' => 'updated test room',
            'description' => 'this is a test room, but updated',
        ] + $component;
        $response = self::$componentHelper->update($uuid, $payload);
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
        $response = self::$componentHelper->delete($uuid);
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
        $response = self::$componentHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
