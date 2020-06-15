<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class ExperienceComponentApiTest extends ApiTestCase
{
    public function testCreateWithNonExistentBoxAndExperience(): void
    {
        $payload = self::$experienceComponentHelper->getDefault([
            'componentGoldenId' => 'non-existent-experience',
            'experienceGoldenId' => 'does-not-exist',
        ]);
        $response = self::$experienceComponentHelper->create($payload);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreateSuccess(): \stdClass
    {
        $response = self::$experienceComponentHelper->create();
        $responseContent = json_decode($response->getContent());
        $this->assertEquals(201, $response->getStatusCode());

        return $responseContent;
    }

    /**
     * @depends testCreateSuccess
     */
    public function testCreateAgainWillFail(\stdClass $componentExperience)
    {
        $payload = self::$experienceComponentHelper->getDefault([
            'componentGoldenId' => $componentExperience->componentGoldenId,
            'experienceGoldenId' => $componentExperience->experienceGoldenId,
        ]);
        $response = self::$experienceComponentHelper->create($payload);

        $this->assertEquals(409, $response->getStatusCode());

        return $componentExperience;
    }

    /**
     * @depends testCreateAgainWillFail
     */
    public function testDelete(\stdClass $componentExperience): \stdClass
    {
        $response = self::$experienceComponentHelper->delete($componentExperience->componentGoldenId, $componentExperience->experienceGoldenId);
        $this->assertEquals(204, $response->getStatusCode());

        return $componentExperience;
    }

    /**
     * @depends testDelete
     */
    public function testDeleteAgain(\stdClass $componentExperience): void
    {
        $response = self::$experienceComponentHelper->delete($componentExperience->componentGoldenId, $componentExperience->experienceGoldenId);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeleteWithNonExistentInformation(): void
    {
        $response = self::$experienceComponentHelper->delete('non-existent-box', 'non-existent-experience');
        $this->assertEquals(404, $response->getStatusCode());
    }
}
