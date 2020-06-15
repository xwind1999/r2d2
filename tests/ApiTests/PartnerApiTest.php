<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class PartnerApiTest extends ApiTestCase
{
    public function testCreateWithInvalidGoldenId()
    {
        $partnerCreateRequest = self::$partnerHelper->getDefault(['goldenId' => '']);
        $response = self::$partnerHelper->create($partnerCreateRequest);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$partnerHelper->create();
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
        $response = self::$partnerHelper->get($uuid);
        $responseContent = json_decode($response->getContent());
        $this->assertObjectHasAttribute('uuid', $responseContent);
        $this->assertObjectHasAttribute('createdAt', $responseContent);
        $this->assertEquals(200, $response->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testGet
     */
    public function testUpdate(string $uuid): string
    {
        $partner = json_decode(self::$partnerHelper->get($uuid)->getContent(), true);
        $payload = [
            'status' => 'inactive',
            'ceaseDate' => (new \DateTime())->format('Y-m-d'),
        ] + $partner;
        $response = self::$partnerHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($payload['status'], $responseContent->status);
        $this->assertEquals(200, $response->getStatusCode());

        return $uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$partnerHelper->delete($uuid);
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
        $response = self::$partnerHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
