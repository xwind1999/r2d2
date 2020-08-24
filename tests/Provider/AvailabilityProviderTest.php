<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\CMHub\CMHub;
use App\Contract\Response\CMHub\CMHubErrorResponse;
use App\Contract\Response\CMHub\CMHubResponse;
use App\Contract\Response\CMHub\GetAvailabilityResponse;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\Partner;
use App\Manager\ComponentManager;
use App\Manager\ExperienceManager;
use App\Manager\RoomAvailabilityManager;
use App\Provider\AvailabilityProvider;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\Provider\AvailabilityProvider
 */
class AvailabilityProviderTest extends TestCase
{
    /**
     * @var CMHub|ObjectProphecy
     */
    protected $cmHub;

    /**
     * @var ObjectProphecy|SerializerInterface
     */
    protected $serializer;

    /**
     * @var ArrayTransformerInterface|ObjectProphecy
     */
    private $arraySerializer;

    /**
     * @var ExperienceManager|ObjectProphecy
     */
    protected $experienceManager;

    /**
     * @var ComponentManager|ObjectProphecy
     */
    protected $componentManager;

    /**
     * @var ObjectProphecy|RoomAvailabilityManager
     */
    protected $roomAvailabilityManager;

    public function setUp(): void
    {
        $this->cmHub = $this->prophesize(CMHub::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->arraySerializer = $this->prophesize(ArrayTransformerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->componentManager = $this->prophesize(ComponentManager::class);
        $this->roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailability
     * @covers \App\Contract\Response\CMHub\GetAvailability\AvailabilityResponse
     */
    public function testGetAvailability()
    {
        $productId = 286201;
        $dateFrom = new \DateTime('2020-04-04');
        $dateTo = new \DateTime('2020-04-04');

        $response = $this->prophesize(ResponseInterface::class);
        $this->cmHub->getAvailability($productId, $dateFrom, $dateTo)->willReturn($response->reveal());

        $argument = '[{"date":"2020-04-28","quantity":40},{"date":"2020-04-29","quantity":40}]';
        $response->getContent()->shouldBeCalled()->willReturn($argument);
        $this->serializer->deserialize(
            $argument,
            sprintf('array<%s>', GetAvailabilityResponse::class), 'json')
            ->willReturn(
                [
                    [
                        'date' => '2020-04-28',
                        'quantity' => 40,
                    ],
            ]
            )->shouldBeCalled();

        $availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal()
        );
        $response = $availabilityProvider->getAvailability($productId, $dateFrom, $dateTo);

        $this->assertInstanceOf(CMHubResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailability
     * @covers \App\Contract\Response\CMHub\CMHubErrorResponse
     */
    public function testGetAvailabilityThrowsHttpException()
    {
        $productId = 286201;
        $dateFrom = new \DateTime('2020-04-04');
        $dateTo = new \DateTime('2020-04-04');

        $availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal()
        );

        $result = $this->prophesize(CMHubErrorResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);

        $this->cmHub->getAvailability($productId, $dateFrom, $dateTo)->willThrow($exception->reveal());
        $exception->getResponse()->willReturn($responseInterface->reveal());
        $responseInterface->toArray(false)->willReturn(['error' => ['code' => 404, 'message' => 'Not Found']]);

        $this->arraySerializer
            ->fromArray(['code' => 404, 'message' => 'Not Found'], CMHubErrorResponse::class)
            ->willReturn($result->reveal())
        ;

        $this->assertInstanceOf(
            CMHubErrorResponse::class,
            $availabilityProvider->getAvailability($productId, $dateFrom, $dateTo))
        ;
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByBoxIdAndDates
     */
    public function testGetRoomAvailabilitiesByBoxId()
    {
        $expIds = [
            '1', '2', '3', '4',
        ];

        $components = [
            '11' => [
                [
                    'goldenId' => '11',
                    'duration' => 2,
                ],
                'experienceGoldenId' => '1',
            ],
            '22' => [
                [
                    'goldenId' => '22',
                    'duration' => 1,
                ],
                'experienceGoldenId' => '2',
            ],
        ];

        $availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal()
        );
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $this->experienceManager->filterListExperienceIdsByBoxId(Argument::any())->willReturn($expIds);
        $this->componentManager->getRoomsByExperienceGoldenIdsList(Argument::any())->willReturn($components);
        $this->roomAvailabilityManager->getRoomAvailabilitiesByMultipleComponentGoldenIds(['11', '22'], $dateFrom, $dateTo)
            ->willReturn(
                [
                    '11' => [
                        'componentGoldenId' => '11',
                        'duration' => 2,
                    ],
                    '22' => [
                        'componentGoldenId' => '22',
                        'duration' => 1,
                    ],
                ]
            );

        $expectedArray = [
            [
                'Package' => '1',
                'Request' => 0,
                'Stock' => 6,
            ],
            [
                'Package' => '2',
                'Request' => 0,
                'Stock' => 6,
            ],
        ];

        $this->assertEquals($expectedArray, $availabilityProvider->getRoomAvailabilitiesByBoxIdAndDates(1, $dateFrom, $dateTo));
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByExperienceAndDates
     */
    public function testGetRoomAvailabilitiesListByExperience()
    {
        $availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal()
        );
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $component = new Component();
        $component->duration = 1;
        $component->isSellable = true;
        $component->isReservable = true;
        $component->goldenId = '1234';
        $experienceComponent = new ExperienceComponent();
        $experienceComponent->component = $component;
        $experienceComponent->isEnabled = true;
        $experienceComponent->componentGoldenId = '1234';
        $collection = new ArrayCollection();
        $collection->add($experienceComponent);
        $experience = new Experience();
        $experience->experienceComponent = $collection;
        $partner = new Partner();
        $partner->status = 'partner';
        $experience->partner = $partner;

        $this->roomAvailabilityManager->getRoomAvailabilitiesByComponentGoldenId(
            Argument::any(), Argument::any(), Argument::any()
        )
            ->willReturn(
                [
                    0 => [
                        'stock' => 10,
                        'date' => new \DateTime('2020-06-21'),
                        'type' => 'stock',
                        'componentGoldenId' => '1234',
                    ],
                    1 => [
                        'stock' => 10,
                        'date' => new \DateTime('2020-06-22'),
                        'type' => 'stock',
                        'componentGoldenId' => '1234',
                    ],
                    2 => [
                        'stock' => 0,
                        'date' => new \DateTime('2020-06-23'),
                        'type' => 'stock',
                        'componentGoldenId' => '1234',
                    ],
                    3 => [
                        'stock' => 10,
                        'date' => new \DateTime('2020-06-24'),
                        'type' => 'on_request',
                        'componentGoldenId' => '1234',
                    ],
                ]
            );

        $expectedArray = [
            'duration' => 1,
            'isSellable' => true,
            'availabilities' => ['0', '1', '1', '0', 'r', '0'],
        ];

        $this->assertEquals($expectedArray, $availabilityProvider->getRoomAvailabilitiesByExperienceAndDates($experience, $dateFrom, $dateTo));
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByExperienceAndDates
     */
    public function testGetRoomAvailabilitiesListByExperienceWithNoData()
    {
        $availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal()
        );
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $experience = new Experience();
        $partner = new Partner();
        $partner->status = 'partner';
        $experience->partner = $partner;

        $expectedArray = [
            'duration' => 1,
            'isSellable' => false,
            'availabilities' => [
                '0', '0', '0', '0', '0', '0',
            ],
        ];

        $this->assertEquals($expectedArray, $availabilityProvider->getRoomAvailabilitiesByExperienceAndDates($experience, $dateFrom, $dateTo));
    }

    /**
     * @covers ::__construct
     *
     * @covers ::getRoomAvailabilitiesByExperienceIdsList
     */
    public function testGetRoomAvailabilitiesByExperienceIdList()
    {
        $availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal()
        );
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $experienceIds = ['1234', '4321'];
        $this->experienceManager
            ->filterIdsListWithPartnerStatus($experienceIds, Argument::any())->willReturn($experienceIds);
        $this->componentManager->getRoomsByExperienceGoldenIdsList(Argument::any())->willReturn(
            [
                '1111' => [
                    [
                        'goldenId' => '1111',
                        'duration' => 1,
                        'partnerGoldenId' => '1234',
                        'isSellable' => true,
                    ],
                    'experienceGoldenId' => '1234',
                ],
                '2222' => [
                    [
                        'goldenId' => '2222',
                        'duration' => 2,
                        'partnerGoldenId' => '4321',
                        'isSellable' => true,
                    ],
                    'experienceGoldenId' => '4321',
                ],
            ]
        );
        $this->roomAvailabilityManager->getRoomAvailabilitiesByMultipleComponentGoldenIds(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn(
            [
                0 => [
                    'stock' => 10,
                    'date' => new \DateTime('2020-06-20'),
                    'type' => 'stock',
                    'componentGoldenId' => '1111',
                ],
                1 => [
                    'stock' => 0,
                    'date' => new \DateTime('2020-06-22'),
                    'type' => 'stock',
                    'componentGoldenId' => '1111',
                ],
                2 => [
                    'stock' => 10,
                    'date' => new \DateTime('2020-06-24'),
                    'type' => 'on_request',
                    'componentGoldenId' => '1111',
                ],
            ]
        );

        $expectedArray = [
            '1234' => [
                'duration' => 1,
                'isSellable' => true,
                'partnerId' => '1234',
                'availabilities' => ['1', '0', '0', '0', 'r', '0'],
            ],
        ];

        $this->assertEquals($expectedArray, $availabilityProvider->getRoomAvailabilitiesByExperienceIdsList($experienceIds, $dateFrom, $dateTo));
    }
}
