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
    /**
     * @dataProvider manageableTestCases
     */
    public function testShouldCalculateComponent($box, $partner, $experience, $component, $boxExperience, $experienceComponent, $expectedToBeManageable)
    {
        static::cleanUp();

        $response = self::$broadcastListenerHelper->testProducts($box);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        self::$container->get(BoxRepository::class)->findOneByGoldenId($box['id']);

        $response = self::$broadcastListenerHelper->testPartners($partner);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PARTNER);
        self::$container->get(PartnerRepository::class)->findOneByGoldenId($partner['id']);

        $response = self::$broadcastListenerHelper->testProducts($experience);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);
        self::$container->get(ExperienceRepository::class)->findOneByGoldenId($experience['id']);

        $response = self::$broadcastListenerHelper->testProducts($component);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);
        self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);

        $response = self::$broadcastListenerHelper->testRelationships($boxExperience);
        $this->assertEquals(202, $response->getStatusCode());
        $this->consume(self::QUEUE_BROADCAST_RELATIONSHIP);

        $response = self::$broadcastListenerHelper->testRelationships($experienceComponent);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_RELATIONSHIP);
        $this->consume(self::QUEUE_CALCULATE_MANAGEABLE_FLAG, 20);

        $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);
        $this->assertEquals($expectedToBeManageable, $component->isManageable);
    }

    /**
     * @see testShouldCalculateComponent
     */
    public function manageableTestCases(): iterable
    {
        $box = [
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
        $partner = [
            'status' => 'partner',
            'currencyCode' => 'EUR',
            'isChannelManagerEnabled' => true,
            'partnerCeaseDate' => null,
        ];
        $experience = [
            'name' => 'experience name',
            'description' => 'experience description',
            'type' => 'experience',
            'productPeopleNumber' => 1,
            'status' => 'active',
        ];
        $component = [
            'name' => 'component name',
            'description' => 'component description',
            'isReservable' => true,
            'isSellable' => true,
            'type' => 'component',
            'roomStockType' => 'stock',
            'stockAllotment' => 1,
            'status' => 'active',
        ];
        $boxExperience = [
            'isEnabled' => true,
            'relationshipType' => 'Box-Experience',
        ];
        $experienceComponent = [
            'isEnabled' => true,
            'relationshipType' => 'Experience-Component',
        ];

        yield 'manageable with live box' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), true];

        $box['status'] = 'prospect';
        yield 'manageable with prospect box' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), true];

        $box['status'] = 'production';
        yield 'manageable with production box' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), true];

        $box['status'] = 'ready';
        yield 'manageable with ready box' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), true];

        $box['status'] = 'redeemable';
        yield 'manageable with redeemable box' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), true];

        $box['status'] = 'obsolete';
        yield 'manageable with obsolete box' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), false];

        $box['status'] = 'live';
        $partner['status'] = 'ceased';
        yield 'manageable with ceased partner' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), false];

        $box['status'] = 'live';
        $partner['status'] = 'partner';
        $component['isReservable'] = false;
        yield 'manageable with non reservable component' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), false];

        $component['isReservable'] = true;
        $boxExperience['isEnabled'] = false;
        yield 'manageable with disabled box-experience relationship' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), false];

        $boxExperience['isEnabled'] = true;
        $experienceComponent['isEnabled'] = false;
        yield 'manageable with disabled experience-component relationship' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), false];

        $boxExperience['isEnabled'] = true;
        $experienceComponent['isEnabled'] = true;
        $experience['status'] = 'inactive';
        yield 'manageable with inactive experience' => [...$this->replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent), false];
    }

    public function replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent): array
    {
        $box['id'] = bin2hex(random_bytes(12));
        $partner['id'] = bin2hex(random_bytes(12));
        $experience['id'] = bin2hex(random_bytes(12));
        $experience['partner'] = [
            'id' => $partner['id'],
        ];
        $component['id'] = bin2hex(random_bytes(12));
        $component['partner'] = [
            'id' => $partner['id'],
        ];
        $boxExperience['parentProduct'] = $box['id'];
        $boxExperience['childProduct'] = $experience['id'];
        $experienceComponent['parentProduct'] = $experience['id'];
        $experienceComponent['childProduct'] = $component['id'];

        return [$box, $partner, $experience, $component, $boxExperience, $experienceComponent];
    }
}
