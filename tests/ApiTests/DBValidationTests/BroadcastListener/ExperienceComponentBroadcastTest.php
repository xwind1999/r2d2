<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Repository\ComponentRepository;
use App\Repository\ExperienceComponentRepository;
use App\Repository\ExperienceRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class ExperienceComponentBroadcastTest extends IntegrationTestCase
{
    public function testCreateExperienceComponent()
    {
        static :: cleanUp();

        $component = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'Test component',
            'description' => 'Test component description',
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'partner' => [
                'id' => '12345678',
            ],
            'roomStockType' => 'stock',
            'productDuration' => 2,
            'status' => 'active',
            'type' => 'component',
            'isSellable' => true,
            'isReservable' => false,
            'listPrice' => [
                'currencyCode' => 'EUR',
                'amount' => 100,
            ],
        ];

        $response = self::$broadcastListenerHelper->testComponentProduct($component);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        $componentEntity = self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);

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
            'price' => 50,
        ];

        $response = self::$broadcastListenerHelper->testExperienceProduct($experience);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        $experienceEntity = self::$container->get(ExperienceRepository::class)->findOneByGoldenId($experience['id']);

        $payload = [
            'parentProduct' => $experience['id'],
            'childProduct' => $component['id'],
            'relationshipType' => 'Experience-Component',
            'isEnabled' => true,
        ];

        $response = self::$broadcastListenerHelper->testExperienceComponentRelationship($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-product-relationship');

        /** @var ExperienceComponentRepository $experienceComponentRepository */
        $experienceComponentRepository = self::$container->get(ExperienceComponentRepository::class);
        $experienceComponent = $experienceComponentRepository->findOneByExperienceComponent($experienceEntity, $componentEntity);
        $this->assertEquals($payload['parentProduct'], $experienceComponent->experienceGoldenId);
        $this->assertEquals($payload['childProduct'], $experienceComponent->componentGoldenId);
        $this->assertEquals($payload['isEnabled'], $experienceComponent->isEnabled);
    }
}
