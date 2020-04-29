<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

class BoxExperienceApiTest extends ApiTestCase
{
    public function testCreateWithNonExistentBoxAndExperience(): void
    {
        $payload = self::$boxExperienceHelper->getDefault([
            'box_golden_id' => 'non-existent-experience',
            'experience_golden_id' => 'does-not-exist',
        ]);
        $response = self::$boxExperienceHelper->create($payload);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreateSuccess(): \stdClass
    {
        $response = self::$boxExperienceHelper->create();
        $responseContent = json_decode($response->getContent());
        $this->assertEquals(201, $response->getStatusCode());

        return $responseContent;
    }

    /**
     * @depends testCreateSuccess
     */
    public function testCreateAgainWillFail(\stdClass $boxExperience)
    {
        $payload = self::$boxExperienceHelper->getDefault([
            'box_golden_id' => $boxExperience->box_golden_id,
            'experience_golden_id' => $boxExperience->experience_golden_id,
        ]);
        $response = self::$boxExperienceHelper->create($payload);

        $this->assertEquals(409, $response->getStatusCode());

        return $boxExperience;
    }

    /**
     * @depends testCreateAgainWillFail
     */
    public function testDelete(\stdClass $boxExperience): \stdClass
    {
        $response = self::$boxExperienceHelper->delete($boxExperience->box_golden_id, $boxExperience->experience_golden_id);
        $this->assertEquals(204, $response->getStatusCode());

        return $boxExperience;
    }

    /**
     * @depends testDelete
     */
    public function testDeleteAgain(\stdClass $boxExperience): void
    {
        $response = self::$boxExperienceHelper->delete($boxExperience->box_golden_id, $boxExperience->experience_golden_id);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeleteWithNonExistentInformation(): void
    {
        $response = self::$boxExperienceHelper->delete('non-existent-box', 'non-existent-experience');
        $this->assertEquals(404, $response->getStatusCode());
    }
}
