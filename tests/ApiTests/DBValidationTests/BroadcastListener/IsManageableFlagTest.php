<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Repository\BoxRepository;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use App\Tests\ApiTests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class IsManageableFlagTest extends IntegrationTestCase
{
    /**
     * @dataProvider manageableTestCases
     */
    public function testShouldCalculateComponent(
        array $box,
        array $partner,
        array $experience,
        array $component,
        array $boxExperience,
        array $experienceComponent,
        bool $expectedToBeManageable
    ): void {
        static::cleanUp();

        $response = self::$broadcastListenerHelper->testBoxProduct($box);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        self::$container->get(BoxRepository::class)->findOneByGoldenId($box['id']);

        $response = self::$broadcastListenerHelper->testPartners($partner);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PARTNER);
        self::$container->get(PartnerRepository::class)->findOneByGoldenId($partner['id']);

        $response = self::$broadcastListenerHelper->testExperienceProduct($experience);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);
        self::$container->get(ExperienceRepository::class)->findOneByGoldenId($experience['id']);

        $response = self::$broadcastListenerHelper->testComponentProduct($component);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);
        self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);

        $response = self::$broadcastListenerHelper->testBoxExperienceRelationship($boxExperience);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());
        $this->consume(self::QUEUE_BROADCAST_RELATIONSHIP);

        $response = self::$broadcastListenerHelper->testExperienceComponentRelationship($experienceComponent);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_RELATIONSHIP);
        $this->consume(self::QUEUE_CALCULATE_MANAGEABLE_FLAG, 20);

        $component = self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);
        self::assertEquals($expectedToBeManageable, $component->isManageable);
    }

    /**
     * @see testShouldCalculateComponent
     */
    public function manageableTestCases(): iterable
    {
        yield 'manageable with live box' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            true,
        ];

        yield 'manageable with prospect box' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(['status' => 'prospect']),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            true,
        ];

        yield 'manageable with production box' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(['status' => 'production']),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            true,
        ];

        yield 'manageable with ready box' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(['status' => 'ready']),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            true,
        ];

        yield 'manageable with redeemable box' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(['status' => 'redeemable']),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            true,
        ];

        yield 'manageable with obsolete box' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(['status' => 'obsolete']),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            false,
        ];

        yield 'manageable with ceased partner' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(['status' => 'ceased']),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            false,
        ];

        yield 'manageable with non reservable component' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(['isReservable' => false]),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            false,
        ];

        yield 'manageable with disabled box-experience relationship' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(['isEnabled' => false]),
                $this->generateDefaultExperienceComponent()
            ),
            false,
        ];

        yield 'manageable with disabled experience-component relationship' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent(['isEnabled' => false])
            ),
            false,
        ];

        yield 'manageable with inactive experience' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(['status' => 'inactive']),
                $this->generateDefaultComponent(),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            false,
        ];

        yield 'component with null duration' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(['productDuration' => null]),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            false,
        ];

        yield 'component with duration=0' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(['productDuration' => 0]),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            false,
        ];

        yield 'component with duration_unit different than Nights' => [
            ...$this->replaceIds(
                $this->generateDefaultBox(),
                $this->generateDefaultPartner(),
                $this->generateDefaultExperience(),
                $this->generateDefaultComponent(['productDurationUnit' => 'Days']),
                $this->generateDefaultBoxExperience(),
                $this->generateDefaultExperienceComponent()
            ),
            false,
        ];
    }

    private function replaceIds($box, $partner, $experience, $component, $boxExperience, $experienceComponent): array
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

    private function generateDefaultBox(array $overrides = []): array
    {
        return $overrides + [
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
    }

    private function generateDefaultPartner(array $overrides = []): array
    {
        return $overrides + [
            'status' => 'partner',
            'currencyCode' => 'EUR',
            'isChannelManagerEnabled' => true,
            'partnerCeaseDate' => null,
        ];
    }

    private function generateDefaultExperience(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'experience name',
            'description' => 'experience description',
            'type' => 'experience',
            'productPeopleNumber' => 1,
            'status' => 'active',
        ];
    }

    private function generateDefaultComponent(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'component name',
            'description' => 'component description',
            'isReservable' => true,
            'isSellable' => true,
            'type' => 'component',
            'roomStockType' => 'stock',
            'stockAllotment' => 1,
            'status' => 'active',
            'productDuration' => 2,
            'productDurationUnit' => 'Nights',
        ];
    }

    private function generateDefaultBoxExperience(array $overrides = []): array
    {
        return $overrides + [
            'isEnabled' => true,
            'relationshipType' => 'Box-Experience',
        ];
    }

    private function generateDefaultExperienceComponent(array $overrides = []): array
    {
        return $overrides + [
            'isEnabled' => true,
            'relationshipType' => 'Experience-Component',
        ];
    }
}
