<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Repository\ExperienceRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class ExperienceBroadcastTest extends IntegrationTestCase
{
    public function testCreateExperience(): string
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'type' => 'experience',
            'name' => '1234',
            'description' => '1234',
            'productPeopleNumber' => '2',
            'partner' => [
                'id' => '1234',
            ],
            'status' => 'active',
            'price' => 500,
        ];

        $response = self::$broadcastListenerHelper->testExperienceProduct($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-product');

        $experienceRepository = self::$container->get(ExperienceRepository::class);
        $experience = $experienceRepository->findOneByGoldenId($payload['id']);
        $this->assertEquals($payload['id'], $experience->goldenId);
        $this->assertEquals($payload['partner']['id'], $experience->partnerGoldenId);
        $this->assertEquals($payload['name'], $experience->name);
        $this->assertEquals($payload['description'], $experience->description);
        $this->assertEquals($payload['productPeopleNumber'], $experience->peopleNumber);
        $this->assertEquals($payload['status'], $experience->status);

        return $experience->goldenId;
    }

    /**
     * @depends testCreateExperience
     */
    public function testUpdateExistingExperienceWithStatusInActive(string $experienceID): void
    {
        $payload = [
            'id' => $experienceID,
            'type' => 'experience',
            'name' => '1234',
            'description' => '1234',
            'productPeopleNumber' => '2',
            'partner' => [
                'id' => '1234',
            ],
            'status' => 'inactive',
            'price' => 500,
        ];

        $response = self::$broadcastListenerHelper->testExperienceProduct($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-product');

        $experienceRepository = self::$container->get(ExperienceRepository::class);
        $partner = $experienceRepository->findOneByGoldenId($payload['id']);
        $this->assertEquals($payload['id'], $partner->goldenId);
        $this->assertEquals($payload['partner']['id'], $partner->partnerGoldenId);
        $this->assertEquals($payload['name'], $partner->name);
        $this->assertEquals($payload['description'], $partner->description);
        $this->assertEquals($payload['productPeopleNumber'], $partner->peopleNumber);
        $this->assertEquals($payload['status'], $partner->status);
    }
}
