<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class ExperienceApiTest extends ApiTestCase
{
    const API_BASE_URL = '/api/experience';

    public function testCreateWithInvalidPartnerGoldenId()
    {
        $experienceCreateRequest = self::$experienceHelper->getDefault(['partner_golden_id' => '']);
        $response = self::$experienceHelper->create($experienceCreateRequest);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testCreateWithNonExistentPartner()
    {
        $experienceCreateRequest = self::$experienceHelper->getDefault(['partner_golden_id' => 'non-existent-partner']);
        $response = self::$experienceHelper->create($experienceCreateRequest);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreateWithExistentGoldenId()
    {
        $experienceCreateRequest = self::$experienceHelper->getDefault();
        self::$experienceHelper->addValidPartner($experienceCreateRequest);
        self::$experienceHelper->create($experienceCreateRequest);
        $response = self::$experienceHelper->create($experienceCreateRequest);
        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testCreateSuccess(): string
    {
        $response = self::$experienceHelper->create();
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
        $response = self::$experienceHelper->get($uuid);
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
        $experience = json_decode(self::$experienceHelper->get($uuid)->getContent(), true);
        $payload = [
            'description' => 'new description for this experience',
        ] + $experience;
        $response = self::$experienceHelper->update($uuid, $payload);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($payload['description'], $responseContent->description);
        $this->assertEquals(200, $response->getStatusCode());

        return $responseContent->uuid;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(string $uuid): string
    {
        $response = self::$experienceHelper->delete($uuid);
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
        $response = self::$experienceHelper->get($uuid);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
