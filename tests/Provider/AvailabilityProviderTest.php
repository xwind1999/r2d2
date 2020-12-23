<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Helper\AvailabilityHelper;
use App\Manager\ComponentManager;
use App\Manager\ExperienceManager;
use App\Manager\RoomAvailabilityManager;
use App\Manager\RoomPriceManager;
use App\Provider\AvailabilityProvider;
use App\Repository\BookingDateRepository;
use App\Tests\ProphecyTestCase;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \App\Provider\AvailabilityProvider
 * @group availability-provider
 */
class AvailabilityProviderTest extends ProphecyTestCase
{
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

    /**
     * @var AvailabilityHelper|ObjectProphecy
     */
    protected $availabilityHelper;

    private AvailabilityProvider $availabilityProvider;

    /**
     * @var BookingDateRepository|ObjectProphecy
     */
    private ObjectProphecy $bookingDateRepository;

    public function setUp(): void
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->arraySerializer = $this->prophesize(ArrayTransformerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->componentManager = $this->prophesize(ComponentManager::class);
        $this->roomAvailabilityManager = $this->prophesize(RoomAvailabilityManager::class);
        $this->roomPriceManager = $this->prophesize(RoomPriceManager::class);
        $this->bookingDateRepository = $this->prophesize(BookingDateRepository::class);
        $this->availabilityHelper = $this->prophesize(AvailabilityHelper::class);
        $this->availabilityProvider = new AvailabilityProvider(
            $this->serializer->reveal(),
            $this->arraySerializer->reveal(),
            $this->experienceManager->reveal(),
            $this->componentManager->reveal(),
            $this->roomAvailabilityManager->reveal(),
            $this->roomPriceManager->reveal(),
            $this->bookingDateRepository->reveal(),
            $this->availabilityHelper->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByBoxIdAndStartDate
     */
    public function testGetRoomAvailabilitiesByBoxId()
    {
        $boxId = '1234';
        $dateFrom = new \DateTime('2020-06-20');

        $expectedArray = [
            [
                'Package' => '1234',
                'Stock' => 1,
                'Request' => 0,
            ],
            [
                'Package' => '1235',
                'Stock' => 0,
                'Request' => 1,
            ],
            [
                'Package' => '1236',
                'Stock' => 1,
                'Request' => 0,
            ],
        ];

        $this->roomAvailabilityManager->getRoomAvailabilitiesByBoxId(
            $boxId,
            $dateFrom
        )->willReturn($expectedArray);

        $this->assertEquals(
            $expectedArray,
            $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate($boxId, $dateFrom)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAndPricesAvailabilitiesByExperienceIdAndDates
     */
    public function testGetRoomAndPriceAvailabilitiesByExperienceIdAndDates()
    {
        $expId = '1234';
        $dateFrom = new \DateTime('2020-06-20');
        $dateTo = new \DateTime('2020-06-23');

        $expectedArray = [
            [
                'date' => '2020-06-20',
                'stock' => '2',
                'componentGoldenId' => '4321',
                'isStopSale' => '0',
                'duration' => '1',
                'price' => '7500',
                'lastBookableDate' => null,
                'experienceGoldenId' => '1234',
                'partnerGoldenId' => '4321',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
            [
                'date' => '2020-06-21',
                'stock' => '2',
                'componentGoldenId' => '4321',
                'isStopSale' => '0',
                'duration' => '1',
                'price' => '7500',
                'lastBookableDate' => null,
                'experienceGoldenId' => '1234',
                'partnerGoldenId' => '4321',
                'isSellable' => '0',
                'roomStockType' => 'stock',
            ],
            [
                'date' => '2020-06-22',
                'stock' => '1',
                'componentGoldenId' => '4321',
                'isStopSale' => '0',
                'duration' => '1',
                'price' => '7500',
                'lastBookableDate' => null,
                'experienceGoldenId' => '1234',
                'partnerGoldenId' => '4321',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
            [
                'date' => '2020-06-23',
                'stock' => '1',
                'componentGoldenId' => '4321',
                'isStopSale' => '0',
                'duration' => '1',
                'price' => '7500',
                'lastBookableDate' => null,
                'experienceGoldenId' => '1234',
                'partnerGoldenId' => '4321',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
        ];

        $this->roomAvailabilityManager->getRoomAndPriceAvailabilitiesByExperienceIdAndDates(
            $expId,
            $dateFrom,
            $dateTo
            )->willReturn($expectedArray);

        $expectedBookingDates = [
            [
                'experienceGoldenId' => '1234',
                'componentGoldenId' => '4321',
                'date' => '2020-06-20',
                'usedStock' => '1',
            ],
            [
                'experienceGoldenId' => '1234',
                'componentGoldenId' => '4321',
                'date' => '2020-06-21',
                'usedStock' => '1',
            ],
        ];

        $this->bookingDateRepository->findBookingDatesByExperiencesAndDates(
            Argument::type('array'),
            Argument::type(\DateTimeInterface::class),
            Argument::type(\DateTimeInterface::class)
        )->willReturn($expectedBookingDates);

        $expectedRealStockResult = $expectedArray;
        foreach ($expectedArray as $key => $item) {
            if (isset($expectedBookingDates[$key]) && $item['date'] === $expectedBookingDates[$key]['date']) {
                $item['stock'] -= $expectedBookingDates[$key]['usedStock'];
                $expectedRealStockResult[$key] = $item;
            }
        }

        $this->availabilityHelper->getRealStock(
            $expectedArray,
            $expectedBookingDates
        )->willReturn($expectedRealStockResult);

        $this->assertEquals(
            $expectedRealStockResult,
            $this->availabilityProvider->getRoomAndPricesAvailabilitiesByExperienceIdAndDates($expId, $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getRoomAvailabilitiesByExperienceIdsList
     */
    public function testGetRoomAvailabilitiesByExperienceIdList()
    {
        $startDate = new \DateTime('2020-06-20');

        $experienceIds = ['1234', '4321'];
        $this->roomAvailabilityManager->getRoomAvailabilitiesByMultipleExperienceGoldenIds(
            $experienceIds,
            $startDate
        )->willReturn(
            [
                [
                    'experience_golden_id' => '1234',
                    'partner_golden_id' => '00112233',
                    'is_sellable' => '1',
                    'duration' => '1',
                ],
            ]
        );

        $expectedArray = [
            '1234' => [
                'duration' => '1',
                'isSellable' => '1',
                'partnerId' => '00112233',
                'experienceId' => '1234',
            ],
        ];

        $this->assertEquals(
            $expectedArray,
            $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList($experienceIds, $startDate)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getManageableComponentForGetPackage
     */
    public function testGetManageableComponentForGetPackage(): void
    {
        $expected = [
            [
                'goldenId' => '227914',
                'duration' => '1',
                'partnerGoldenId' => '00037411',
                'isSellable' => '0',
                'roomStockType' => 'stock',
            ],
        ];
        $this->componentManager
            ->getManageableComponentForGetPackage(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn($expected)
        ;
        $result = $this->availabilityProvider->getManageableComponentForGetPackage('12345');
        $this->assertEquals($expected, $result);
        $this->assertIsArray($result);
    }
}
