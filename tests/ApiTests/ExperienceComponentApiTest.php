<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class ExperienceComponentApiTest extends ApiTestCase
{
    public function testCreateWithNonExistentBoxAndExperience(): void
    {
        $payload = self::$experienceComponentHelper->getDefault([
            'room_golden_id' => 'non-existent-experience',
            'experience_golden_id' => 'does-not-exist',
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
    public function testCreateAgainWillFail(\stdClass $roomExperience)
    {
        $payload = self::$experienceComponentHelper->getDefault([
            'room_golden_id' => $roomExperience->room_golden_id,
            'experience_golden_id' => $roomExperience->experience_golden_id,
        ]);
        $response = self::$experienceComponentHelper->create($payload);

        $this->assertEquals(409, $response->getStatusCode());

        return $roomExperience;
    }

    /**
     * @depends testCreateAgainWillFail
     */
    public function testDelete(\stdClass $roomExperience): \stdClass
    {
        $response = self::$experienceComponentHelper->delete($roomExperience->room_golden_id, $roomExperience->experience_golden_id);
        $this->assertEquals(204, $response->getStatusCode());

        return $roomExperience;
    }

    /**
     * @depends testDelete
     */
    public function testDeleteAgain(\stdClass $roomExperience): void
    {
        $response = self::$experienceComponentHelper->delete($roomExperience->room_golden_id, $roomExperience->experience_golden_id);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeleteWithNonExistentInformation(): void
    {
        $response = self::$experienceComponentHelper->delete('non-existent-box', 'non-existent-experience');
        $this->assertEquals(404, $response->getStatusCode());
    }
}
