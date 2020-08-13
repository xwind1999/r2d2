<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\BroadcastListener;

use App\Repository\BoxRepository;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class IsManageableFlagTest extends IntegrationTestCase
{
    public function testShouldCalculateComponent()
    {
        $box = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'product name',
            'description' => 'product description',
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'universe' => [
                'id' => 'STA',
            ],
            'sellableCountry' => [
                'code' => 'FR',
            ],
            'status' => 'live',
            'type' => 'mev',
        ];

        $response = self::$broadcastListenerHelper->testProducts($box);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        self::$container->get(BoxRepository::class)->findOneByGoldenId($box['id']);

        $partner = [
            'id' => bin2hex(random_bytes(12)),
            'status' => 'partner',
            'currencyCode' => 'EUR',
            'isChannelManagerEnabled' => true,
            'partnerCeaseDate' => null,
        ];

        $response = self::$broadcastListenerHelper->testPartners($partner);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PARTNER);
        self::$container->get(PartnerRepository::class)->findOneByGoldenId($partner['id']);

        $experience = [
            'id' => bin2hex(random_bytes(12)),
            'partner' => [
                'id' => $partner['id'],
            ],
            'name' => 'experience name',
            'description' => 'experience description',
            'type' => 'experience',
            'productPeopleNumber' => 1,
            'status' => 'active',
        ];

        $response = self::$broadcastListenerHelper->testProducts($experience);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);
        self::$container->get(ExperienceRepository::class)->findOneByGoldenId($experience['id']);

        $component = [
            'id' => bin2hex(random_bytes(12)),
            'partner' => [
                'id' => $partner['id'],
            ],
            'name' => 'component name',
            'description' => 'component description',
            'isReservable' => true,
            'isSellable' => true,
            'type' => 'component',
            'roomStockType' => 'stock',
            'stockAllotment' => 1,
            'status' => 'active',
        ];

        $response = self::$broadcastListenerHelper->testProducts($component);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);
        self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);

        $boxExperience = [
            'parentProduct' => $box['id'],
            'childProduct' => $experience['id'],
            'isEnabled' => true,
            'relationshipType' => 'Box-Experience',
        ];
        $response = self::$broadcastListenerHelper->testRelationships($boxExperience);
        $this->assertEquals(202, $response->getStatusCode());
        $this->consume(self::QUEUE_BROADCAST_RELATIONSHIP);

        $experienceComponent = [
            'parentProduct' => $experience['id'],
            'childProduct' => $component['id'],
            'isEnabled' => true,
            'relationshipType' => 'Experience-Component',
        ];
        $response = self::$broadcastListenerHelper->testRelationships($experienceComponent);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_RELATIONSHIP);
        $this->consume(self::QUEUE_CALCULATE_MANAGEABLE_FLAG, 20);

        $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);
        $this->assertTrue($component->isManageable);
    }
}
