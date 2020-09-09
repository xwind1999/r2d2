<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\CMHub\CMHub;
use App\Contract\Response\CMHub\CMHubErrorResponse;
use App\Contract\Response\CMHub\CMHubResponse;
use App\Contract\Response\CMHub\GetAvailabilityResponse;
use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Entity\Partner;
use App\Entity\RoomPrice;
use App\Manager\ComponentManager;
use App\Manager\ExperienceManager;
use App\Manager\RoomAvailabilityManager;
use App\Manager\RoomPriceManager;
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

    /**
     * @var ObjectProphecy|RoomPriceManager
     */
    protected $roomPriceManager;

    private AvailabilityProvider $availabilityProvider;

    public function setUp(): void
    {
        $this->cmHub = $this->prophesize(CMHub::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->arraySerializer = $this->prophesize(ArrayTransformerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->componentManager = $this->prophesize(ComponentManager::class);
        $this->roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $this->roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $this->availabilityProvider = new AvailabilityProvider(
            $this->cmHub->reveal(),
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal(),
            $this->roomPriceManager->reveal()
        );
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

        $response = $this->availabilityProvider->getAvailability($productId, $dateFrom, $dateTo);

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
            $this->availabilityProvider->getAvailability($productId, $dateFrom, $dateTo))
        ;
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByBoxIdAndStartDate
     */
    public function testGetRoomAvailabilitiesByBoxId()
    {
        $boxId = '1234';
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $expectedArray = [
            [
                'Package' => '1234',
                'Stock' => 3,
                'Request' => 0,
            ],
            [
                'Package' => '1235',
                'Stock' => 0,
                'Request' => 3,
            ],
            [
                'Package' => '1236',
                'Stock' => 2,
                'Request' => 1,
            ],
        ];

        $this->roomAvailabilityManager->getRoomAvailabilitiesByBoxId(
            $boxId,
            Argument::any(),
            Argument::any()
        )->willReturn($expectedArray);

        $this->assertEquals(
            $expectedArray,
            $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate($boxId, $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByExperienceAndDates
     * @covers ::validatePartnerCeaseDate
     * @covers ::getRoomAvailabilitiesAndFilterCeasePartnerByComponent
     */
    public function testGetRoomAvailabilitiesListByExperience()
    {
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $component = new Component();
        $component->duration = 1;
        $component->isSellable = true;
        $component->isReservable = true;
        $component->goldenId = '1234';
        $box = new Box();
        $boxExperience = new BoxExperience();
        $boxExperience->box = $box;
        $experience = new Experience();
        $experience->boxExperience = new ArrayCollection([$boxExperience]);
        $experienceComponent = new ExperienceComponent();
        $experienceComponent->component = $component;
        $experienceComponent->isEnabled = true;
        $experienceComponent->componentGoldenId = '1234';
        $experienceComponent->experience = $experience;
        $collection = new ArrayCollection();
        $collection->add($experienceComponent);
        $experience = new Experience();
        $experience->experienceComponent = $collection;
        $partner = new Partner();
        $partner->status = 'partner';
        $experience->partner = $partner;

        $this->roomAvailabilityManager->getRoomAvailabilitiesByComponent(
            Argument::any(), Argument::any(), Argument::any()
        )
            ->willReturn(
                [
                    '2020-06-21' => [
                        'stock' => 10,
                        'date' => new \DateTime('2020-06-21'),
                        'type' => 'stock',
                        'componentGoldenId' => '1234',
                        'isStopSale' => false,
                    ],
                    '2020-06-22' => [
                        'stock' => 10,
                        'date' => new \DateTime('2020-06-22'),
                        'type' => 'stock',
                        'componentGoldenId' => '1234',
                        'isStopSale' => false,
                    ],
                    '2020-06-23' => [
                        'stock' => 0,
                        'date' => new \DateTime('2020-06-23'),
                        'type' => 'stock',
                        'componentGoldenId' => '1234',
                        'isStopSale' => false,
                    ],
                    '2020-06-24' => [
                        'stock' => 10,
                        'date' => new \DateTime('2020-06-24'),
                        'type' => 'on_request',
                        'componentGoldenId' => '1234',
                        'isStopSale' => false,
                    ],
                ]
            );

        $prices = [
            '2020-06-20' => (function () {
                $roomPrice = new RoomPrice();
                $roomPrice->price = 1000;

                return $roomPrice;
            })(),
            '2020-06-21' => (function () {
                $roomPrice = new RoomPrice();
                $roomPrice->price = 1000;

                return $roomPrice;
            })(),
            '2020-06-22' => (function () {
                $roomPrice = new RoomPrice();
                $roomPrice->price = 1000;

                return $roomPrice;
            })(),
            '2020-06-23' => (function () {
                $roomPrice = new RoomPrice();
                $roomPrice->price = 1000;

                return $roomPrice;
            })(),
        ];

        $this
            ->roomPriceManager
            ->getRoomPricesByComponentAndDateRange($component, $dateFrom, $dateTo)
            ->willReturn($prices);

        $expectedArray = [
            'duration' => 1,
            'isSellable' => true,
            'availabilities' => [
                '2020-06-20' => ['stock' => 0, 'date' => new \DateTime('2020-06-20'), 'type' => 'stock', 'componentGoldenId' => '1234', 'isStopSale' => true],
                '2020-06-21' => ['stock' => 10, 'date' => new \DateTime('2020-06-21'), 'type' => 'stock', 'componentGoldenId' => '1234', 'isStopSale' => false],
                '2020-06-22' => ['stock' => 10, 'date' => new \DateTime('2020-06-22'), 'type' => 'stock', 'componentGoldenId' => '1234', 'isStopSale' => false],
                '2020-06-23' => ['stock' => 0, 'date' => new \DateTime('2020-06-23'), 'type' => 'stock', 'componentGoldenId' => '1234', 'isStopSale' => false],
                '2020-06-24' => ['stock' => 10, 'date' => new \DateTime('2020-06-24'), 'type' => 'on_request', 'componentGoldenId' => '1234', 'isStopSale' => false],
                '2020-06-25' => ['stock' => 0, 'date' => new \DateTime('2020-06-25'), 'type' => 'stock', 'componentGoldenId' => '1234', 'isStopSale' => true],
            ],
            'prices' => [
                '2020-06-20' => $prices['2020-06-20'],
                '2020-06-21' => $prices['2020-06-21'],
                '2020-06-22' => $prices['2020-06-22'],
                '2020-06-23' => $prices['2020-06-23'],
            ],
        ];

        $this->assertEquals(
            $expectedArray,
            $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates($experience, $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByExperienceAndDates
     * @covers ::validatePartnerCeaseDate
     * @covers ::getRoomAvailabilitiesAndFilterCeasePartnerByComponent
     */
    public function testGetRoomAvailabilitiesListByExperienceWithNoData()
    {
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
                '2020-06-20' => ['stock' => 0, 'date' => new \DateTime('2020-06-20'), 'type' => 'stock', 'componentGoldenId' => '', 'isStopSale' => true],
                '2020-06-21' => ['stock' => 0, 'date' => new \DateTime('2020-06-21'), 'type' => 'stock', 'componentGoldenId' => '', 'isStopSale' => true],
                '2020-06-22' => ['stock' => 0, 'date' => new \DateTime('2020-06-22'), 'type' => 'stock', 'componentGoldenId' => '', 'isStopSale' => true],
                '2020-06-23' => ['stock' => 0, 'date' => new \DateTime('2020-06-23'), 'type' => 'stock', 'componentGoldenId' => '', 'isStopSale' => true],
                '2020-06-24' => ['stock' => 0, 'date' => new \DateTime('2020-06-24'), 'type' => 'stock', 'componentGoldenId' => '', 'isStopSale' => true],
                '2020-06-25' => ['stock' => 0, 'date' => new \DateTime('2020-06-25'), 'type' => 'stock', 'componentGoldenId' => '', 'isStopSale' => true],
            ],
            'prices' => [],
        ];
        $this->assertEquals($expectedArray, $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates($experience, $dateFrom, $dateTo));
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByExperienceIdsList
     * @covers ::prepareRoomAvailabilitiesFromComponentsExperiencesAndDates
     * @covers ::getRoomAvailabilitiesAndFilterCeasePartner
     * @covers ::validatePartnerCeaseDate
     */
    public function testGetRoomAvailabilitiesByExperienceIdList()
    {
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-25');

        $experienceIds = ['1234', '4321'];
        $this->experienceManager
            ->filterIdsListWithExperienceIds($experienceIds)
            ->willReturn(
                [
                    '1234' => [
                        'goldenId' => '1234',
                        'ceaseDate' => new \DateTime('2020-06-25'),
                        'status' => 'partner',
                    ],
                    '4321' => [
                        'goldenId' => '4321',
                        'ceaseDate' => null,
                        'status' => 'partner',
                    ],
                ]
            )
        ;
        $this->componentManager->getRoomsByExperienceGoldenIdsList(Argument::any())->willReturn(
            [
                '1111' => [
                    'goldenId' => '1111',
                    'duration' => 1,
                    'partnerGoldenId' => '1234',
                    'isSellable' => true,
                    'experienceGoldenId' => '1234',
                ],
            ]
        );
        $this->roomAvailabilityManager->getRoomAvailabilitiesByComponentGoldenIdAndDates(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn(
            [
                '2020-06-20' => [
                    'stock' => 10,
                    'date' => new \DateTime('2020-06-20'),
                    'type' => 'stock',
                    'componentGoldenId' => '1111',
                    'isStopSale' => false,
                ],
                '2020-06-22' => [
                    'stock' => 0,
                    'date' => new \DateTime('2020-06-22'),
                    'type' => 'stock',
                    'componentGoldenId' => '1111',
                    'isStopSale' => true,
                ],
                '2020-06-24' => [
                    'stock' => 10,
                    'date' => new \DateTime('2020-06-24'),
                    'type' => 'on_request',
                    'componentGoldenId' => '1111',
                    'isStopSale' => false,
                ],
            ]
        );

        $expectedArray = [
            '1111' => [
                'duration' => 1,
                'isSellable' => true,
                'partnerId' => '1234',
                'experienceId' => '1234',
                'availabilities' => [
                    '2020-06-20' => ['stock' => 10, 'type' => 'stock', 'date' => new \DateTime('2020-06-20'), 'componentGoldenId' => '1111', 'isStopSale' => false],
                    '2020-06-21' => ['stock' => 0, 'type' => 'stock', 'date' => new \DateTime('2020-06-21'), 'componentGoldenId' => '1111', 'isStopSale' => true],
                    '2020-06-22' => ['stock' => 0, 'type' => 'stock', 'date' => new \DateTime('2020-06-22'), 'componentGoldenId' => '1111', 'isStopSale' => true],
                    '2020-06-23' => ['stock' => 0, 'type' => 'stock', 'date' => new \DateTime('2020-06-23'), 'componentGoldenId' => '1111', 'isStopSale' => true],
                    '2020-06-24' => ['stock' => 0, 'type' => 'on_request', 'date' => new \DateTime('2020-06-24'), 'componentGoldenId' => '1111', 'isStopSale' => false],
                    '2020-06-25' => ['stock' => 0, 'type' => 'stock', 'date' => new \DateTime('2020-06-25'), 'componentGoldenId' => '1111', 'isStopSale' => true],
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList($experienceIds, $dateFrom, $dateTo));
    }
}
