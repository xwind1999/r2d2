<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Cache\QuickDataCache;
use App\Contract\Response\QuickData\AvailabilityPricePeriodResponse;
use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Event\QuickData\BoxCacheErrorEvent;
use App\Event\QuickData\BoxCacheHitEvent;
use App\Event\QuickData\BoxCacheMissEvent;
use App\Exception\Cache\ResourceNotCachedException;
use App\Manager\ExperienceManager;
use App\Provider\AvailabilityProvider;
use App\Provider\LegacyAvailabilityProvider;
use JMS\Serializer\ArrayTransformerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \App\Provider\LegacyAvailabilityProvider
 */
class LegacyAvailabilityProviderTest extends TestCase
{
    /**
     * @var ArrayTransformerInterface|ObjectProphecy
     */
    protected $serializer;

    /**
     * @var ExperienceManager|ObjectProphecy
     */
    protected $experienceManager;

    /**
     * @var AvailabilityProvider|ObjectProphecy
     */
    protected $availabilityProvider;

    /**
     * @var ObjectProphecy|QuickDataCache
     */
    protected $quickDataCache;

    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    protected $eventDispatcher;

    protected LegacyAvailabilityProvider $legacyAvailabilityProvider;

    public function setUp(): void
    {
        $this->serializer = $this->prophesize(ArrayTransformerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->availabilityProvider = $this->prophesize(AvailabilityProvider::class);
        $this->quickDataCache = $this->prophesize(QuickDataCache::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal(),
            $this->quickDataCache->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperience()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn(
            [
                [
                    'stock' => '1',
                    'experienceGoldenId' => '1',
                    'date' => '2020-10-01',
                    'partnerGoldenId' => '1',
                    'isSellable' => '1',
                    'roomStockType' => 'stock',
                    'duration' => 1,
                ],
                [
                    'stock' => '1',
                    'experienceGoldenId' => '1',
                    'date' => '2020-10-02',
                    'partnerGoldenId' => '1',
                    'isSellable' => '1',
                    'roomStockType' => 'stock',
                    'duration' => 1,
                ],
            ]
        );

        $this->assertInstanceOf(
            GetPackageResponse::class,
            $this->legacyAvailabilityProvider->getAvailabilityForExperience('1234', $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperienceWithNoAvailabilities()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $result = $this->prophesize(QuickDataErrorResponse::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn([]);

        $this->assertInstanceOf(
            QuickDataErrorResponse::class,
            $this->legacyAvailabilityProvider->getAvailabilityForExperience('31209470194830912', $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBoxAndStartDate
     */
    public function testGetAvailabilitiesForBox()
    {
        $boxId = '1234';
        $startDate = new \DateTime('2020-01-01');

        $returnArray = [
            [
                'roomStockType' => 'stock',
                'experienceGoldenId' => '1234',
            ],
            [
                'roomStockType' => 'on_request',
                'experienceGoldenId' => '1235',
            ],
            [
                'roomStockType' => 'allotment',
                'experienceGoldenId' => '1236',
            ],
        ];

        $result = $this->prophesize(GetRangeResponse::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn($returnArray);

        $expected['PackagesList'] = $returnArray;

        $this->quickDataCache->getBoxDate($boxId, $startDate->format('Y-m-d'))->willThrow(new ResourceNotCachedException());
        $this->quickDataCache->setBoxDate($boxId, $startDate->format('Y-m-d'), Argument::type(GetRangeResponse::class))->shouldBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(new BoxCacheMissEvent($boxId, $startDate->format('Y-m-d')))
            ->willReturn(new \stdClass())
            ->shouldBeCalled();

        $result = $this->legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $startDate);

        $this->assertInstanceOf(GetRangeResponse::class, $result);
        $this->quickDataCache->setBoxDate($boxId, $startDate->format('Y-m-d'), $result)->shouldHaveBeenCalled();
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBoxAndStartDate
     */
    public function testGetAvailabilityForBoxFromCache()
    {
        $boxId = '1234';
        $startDate = new \DateTime('2020-01-01');

        $cacheResult = new GetRangeResponse();
        $cacheResult->packagesList = ['1234'];

        $this->quickDataCache->getBoxDate($boxId, $startDate->format('Y-m-d'))->willReturn($cacheResult);

        $this
            ->eventDispatcher
            ->dispatch(new BoxCacheHitEvent($boxId, $startDate->format('Y-m-d')))
            ->shouldBeCalled();

        $result = $this->legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $startDate);

        $this->assertSame($cacheResult, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBoxAndStartDate
     */
    public function testGetAvailabilitiesForBoxFromCacheWillFailDueToResourceBeingCached()
    {
        $boxId = '1234';
        $startDate = new \DateTime('2020-01-01');

        $returnArray = [];

        $result = $this->prophesize(GetRangeResponse::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn($returnArray);

        $expected['PackagesList'] = [];
        $this->quickDataCache->getBoxDate($boxId, $startDate->format('Y-m-d'))->willThrow(new ResourceNotCachedException());
        $this->quickDataCache->setBoxDate($boxId, $startDate->format('Y-m-d'), Argument::type(GetRangeResponse::class))->shouldBeCalled();
        $this->eventDispatcher->dispatch(new BoxCacheMissEvent($boxId, $startDate->format('Y-m-d')))->shouldBeCalled();

        $result = $this->legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $startDate);
        $this->assertInstanceOf(GetRangeResponse::class, $result);
        $this->quickDataCache->setBoxDate($boxId, $startDate->format('Y-m-d'), $result)->shouldHaveBeenCalled();
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBoxAndStartDate
     */
    public function testGetAvailabilitiesForBoxFromCacheWillFailDueToError()
    {
        $boxId = '1234';
        $startDate = new \DateTime('2020-01-01');

        $returnArray = [];

        $result = $this->prophesize(GetRangeResponse::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn($returnArray);

        $expected['PackagesList'] = [];
        $exception = new \Exception();
        $this->quickDataCache->getBoxDate($boxId, $startDate->format('Y-m-d'))->willThrow($exception);
        $this->quickDataCache->setBoxDate($boxId, $startDate->format('Y-m-d'), Argument::type(GetRangeResponse::class))->shouldBeCalled();
        $this->eventDispatcher->dispatch(new BoxCacheErrorEvent($boxId, $startDate->format('Y-m-d'), $exception))->shouldBeCalled();

        $result = $this->legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $startDate);
        $this->assertInstanceOf(GetRangeResponse::class, $result);
        $this->quickDataCache->setBoxDate($boxId, $startDate->format('Y-m-d'), $result)->shouldHaveBeenCalled();
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForMultipleExperiences
     */
    public function testGetAvailabilityForMultipleExperiences()
    {
        $experienceIds = [1234, 5678];
        $startDate = new \DateTime('2020-01-01');

        $result = $this->prophesize(GetPackageV2Response::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $returnArray = [
            '4444' => [
                'duration' => 1,
                'isSellable' => true,
                'partnerId' => '123',
                'experienceId' => '1234',
                'availabilities' => [
                    '2020-01-01' => [
                        'stock' => 1,
                        'type' => 'stock',
                        'isStopSale' => false,
                    ],
                    '2020-01-02' => [
                        'stock' => 1,
                        'type' => 'stock',
                        'isStopSale' => false,
                    ],
                    '2020-01-03' => [
                        'stock' => 1,
                        'type' => 'stock',
                        'isStopSale' => false,
                    ],
                    '2020-01-04' => [
                        'stock' => 1,
                        'type' => 'stock',
                        'isStopSale' => false,
                    ],
                    '2020-01-05' => [
                        'stock' => 1,
                        'type' => 'stock',
                        'isStopSale' => false,
                    ],
                ],
            ],
        ];

        $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList($experienceIds, $startDate)
            ->willReturn($returnArray);
        $response = $this->legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $startDate);

        $this->assertInstanceOf(GetPackageV2Response::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForMultipleExperiences
     */
    public function testGetAvailabilityForMultipleExperiencesWithException()
    {
        $experienceIds = [1234, 5678];
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-05');

        $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList(Argument::any(), Argument::any(), Argument::any())
            ->willThrow(new \Exception());
        $result = $this->prophesize(GetPackageV2Response::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $response = $this->legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageV2Response::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityPriceForExperience
     * @covers ::isCeasedPartnerDate
     */
    public function testGetAvailabilityPriceForExperience()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-02');
        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->availabilityProvider
            ->getRoomAndPricesAvailabilitiesByExperienceIdAndDates('1234', $dateFrom, $dateTo)
            ->willReturn(
                [
                    0 => [
                        'date' => '2020-06-20T00:00:00.000000',
                        'stock' => '1',
                        'type' => 'stock',
                        'isStopSale' => '0',
                        'duration' => '1',
                        'price' => '86.45',
                        'lastBookableDate' => null,
                    ],
                    1 => [
                        'date' => '2020-06-21T00:00:00.000000',
                        'stock' => '1',
                        'type' => 'stock',
                        'isStopSale' => '0',
                        'duration' => '1',
                        'price' => '86.45',
                        'lastBookableDate' => '2020-06-20T00:00:00.000000',
                    ],
                    1 => [
                        'date' => '2020-06-21T00:00:00.000000',
                        'stock' => '1',
                        'type' => 'stock',
                        'isStopSale' => '0',
                        'duration' => '1',
                        'price' => '0',
                        'lastBookableDate' => '2020-06-20T00:00:00.000000',
                    ],
                ]
            )
        ;

        $this->assertInstanceOf(
            AvailabilityPricePeriodResponse::class,
            $this->legacyAvailabilityProvider->getAvailabilityPriceForExperience('1234', $dateFrom, $dateTo)
        );
    }
}
