<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Repository\BoxExperienceRepository;
use App\Repository\BoxRepository;
use App\Repository\ExperienceRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class BoxExperienceBroadcastTest extends IntegrationTestCase
{
    public function testCreateBoxExperience(): array
    {
        static :: cleanUp();

        $box = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'product name',
            'description' => 'product description',
            'universe' => [
                'id' => 'STA',
            ],
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'sellableCountry' => [
                'code' => 'FR',
            ],
            'status' => 'live',
            'type' => 'mev',
        ];

        $response = self::$broadcastListenerHelper->testBoxProduct($box);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        $boxEntity = self::$container->get(BoxRepository::class)->findOneByGoldenId($box['id']);

        $experience = [
            'id' => bin2hex(random_bytes(12)),
            'type' => 'experience',
            'name' => 'Experience Test',
            'description' => 'Experience description',
            'productPeopleNumber' => '2',
            'partner' => [
                'id' => '1234',
            ],
            'status' => 'active',
            'price' => 500,
        ];

        $response = self::$broadcastListenerHelper->testExperienceProduct($experience);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        $experienceEntity = self::$container->get(ExperienceRepository::class)->findOneByGoldenId($experience['id']);

        $payload = [
            'parentProduct' => $box['id'],
            'childProduct' => $experience['id'],
            'relationshipType' => 'Box-Experience',
            'isEnabled' => true,
        ];

        $response = self::$broadcastListenerHelper->testBoxExperienceRelationship($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-product-relationship');

        $boxExperienceRepository = self::$container->get(BoxExperienceRepository::class);
        $boxExperience = $boxExperienceRepository->findOneEnabledByBoxExperience($boxEntity, $experienceEntity);
        $this->assertEquals($payload['parentProduct'], $boxExperience->boxGoldenId);
        $this->assertEquals($payload['childProduct'], $boxExperience->experienceGoldenId);
        $this->assertEquals($payload['isEnabled'], $boxExperience->isEnabled);

        $boxID = $boxExperience->boxGoldenId;
        $experienceID = $boxExperience->experienceGoldenId;

        return [$boxID, $experienceID];
    }

    /**
     * @depends testCreateBoxExperience
     */
    public function testUpdateBoxExperienceToDisable(array $params): void
    {
        self::bootKernel();
        $boxEntity = self::$container->get(BoxRepository::class)->findOneByGoldenId($params[0]);
        $experienceEntity = self::$container->get(ExperienceRepository::class)->findOneByGoldenId($params[1]);

        $payload = [
            'parentProduct' => $params[0],
            'childProduct' => $params[1],
            'relationshipType' => 'Box-Experience',
            'isEnabled' => false,
        ];

        $response = self::$broadcastListenerHelper->testBoxExperienceRelationship($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-product-relationship');

        $boxExperienceRepository = self::$container->get(BoxExperienceRepository::class);
        $boxExperience = $boxExperienceRepository->findOneByBoxExperience($boxEntity, $experienceEntity);
        $this->assertEquals($payload['parentProduct'], $boxExperience->boxGoldenId);
        $this->assertEquals($payload['childProduct'], $boxExperience->experienceGoldenId);
        $this->assertEquals($payload['isEnabled'], $boxExperience->isEnabled);
    }
}
