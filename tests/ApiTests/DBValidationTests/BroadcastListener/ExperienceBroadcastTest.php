<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Repository\ExperienceRepository;
use App\Tests\ApiTests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

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
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $experienceRepository = self::$container->get(ExperienceRepository::class);
        $experience = $experienceRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $experience->goldenId);
        self::assertEquals($payload['partner']['id'], $experience->partnerGoldenId);
        self::assertEquals($payload['name'], $experience->name);
        self::assertEquals($payload['description'], $experience->description);
        self::assertEquals($payload['productPeopleNumber'], $experience->peopleNumber);
        self::assertEquals($payload['status'], $experience->status);

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
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $experienceRepository = self::$container->get(ExperienceRepository::class);
        $partner = $experienceRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $partner->goldenId);
        self::assertEquals($payload['partner']['id'], $partner->partnerGoldenId);
        self::assertEquals($payload['name'], $partner->name);
        self::assertEquals($payload['description'], $partner->description);
        self::assertEquals($payload['productPeopleNumber'], $partner->peopleNumber);
        self::assertEquals($payload['status'], $partner->status);
    }
}
