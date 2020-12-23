<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Cache\QuickDataCache;
use App\Constants\DateTimeConstants;
use App\Contract\Response\QuickData\AvailabilityPricePeriodResponse;
use App\Contract\Response\QuickData\Error\ResponseStatus;
use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Event\QuickData\BoxCacheErrorEvent;
use App\Event\QuickData\BoxCacheHitEvent;
use App\Event\QuickData\BoxCacheMissEvent;
use App\Exception\Cache\ResourceNotCachedException;
use App\Helper\AvailabilityHelper;
use App\Manager\ExperienceManager;
use App\Provider\AvailabilityProvider;
use App\Provider\LegacyAvailabilityProvider;
use App\Tests\ProphecyTestCase;
use JMS\Serializer\ArrayTransformerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @coversDefaultClass \App\Provider\LegacyAvailabilityProvider
 */
class LegacyAvailabilityProviderTest extends ProphecyTestCase
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
     * @var AvailabilityHelper|ObjectProphecy
     */
    protected $availabilityHelper;

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
        $this->availabilityHelper = $this->prophesize(AvailabilityHelper::class);
        $this->quickDataCache = $this->prophesize(QuickDataCache::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal(),
            $this->quickDataCache->reveal(),
            $this->eventDispatcher->reveal(),
            $this->availabilityHelper->reveal()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperience()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-02');

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result->reveal());

        $availabilities = [
            [
                'date' => '20201001',
                'stock' => '1',
                'componentGoldenId' => '4321',
                'isStopSale' => '0',
                'lastBookableDate' => null,
                'duration' => 1,
                'price' => '7500',
                'experienceGoldenId' => '1',
                'partnerGoldenId' => '1',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
            [
                'date' => '20201002',
                'stock' => '1',
                'componentGoldenId' => '4321',
                'isStopSale' => 0,
                'lastBookableDate' => null,
                'price' => '7500',
                'duration' => 1,
                'experienceGoldenId' => '1',
                'partnerGoldenId' => '1',
                'isSellable' => '1',
                'roomStockType' => 'stock',
            ],
        ];

        $this->availabilityProvider->getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
            Argument::type('string'),
            Argument::type(\DateTimeInterface::class),
            Argument::type(\DateTimeInterface::class)
        )->willReturn($availabilities);

        $this->availabilityHelper->fillMissingAvailabilityForGetPackage(
            Argument::type('array'),
            Argument::type('string'),
            Argument::type('int'),
            Argument::type('string'),
            Argument::type('bool'),
            Argument::type(\DateTimeInterface::class),
            Argument::type(\DateTimeInterface::class)
        )->willReturn($availabilities);

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
        $experienceId = '31209470194830912';
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $result = new QuickDataErrorResponse();
        $responseStatus = new ResponseStatus();
        $responseStatus->errorCode = 'NotFoundException';
        $responseStatus->message = 'Resource not found';
        $result->responseStatus = $responseStatus;

        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result);

        $this->availabilityProvider
            ->getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
                $experienceId,
                $dateFrom,
                $dateTo)
            ->willReturn([])
        ;
        $this->availabilityProvider
            ->getManageableComponentForGetPackage($experienceId)
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $result = $this->legacyAvailabilityProvider->getAvailabilityForExperience($experienceId, $dateFrom, $dateTo);

        $this->assertInstanceOf(QuickDataErrorResponse::class, $result);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result->getHttpCode());
        $this->assertEquals('NotFoundException', $result->responseStatus->errorCode);
        $this->assertEquals('Resource not found', $result->responseStatus->message);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperienceWithNoAvailabilitiesAndValidComponent()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-02');

        $result = $this->prophesize(QuickDataErrorResponse::class);
        $this->serializer
            ->fromArray(Argument::type('array'), Argument::type('string'))
            ->willReturn($result->reveal())
            ->shouldBeCalledOnce()
        ;

        $this->availabilityProvider
            ->getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
                Argument::type('string'),
                Argument::type(\DateTimeInterface::class),
                Argument::type(\DateTimeInterface::class))
            ->willReturn([])
        ;
        $this->availabilityProvider
            ->getManageableComponentForGetPackage(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn(
                [
                    [
                        'goldenId' => '227914',
                        'duration' => '1',
                        'partnerGoldenId' => '00037411',
                        'isSellable' => '0',
                        'roomStockType' => 'stock',
                    ],
                ]
        );

        $this->availabilityHelper
            ->fillMissingAvailabilityForGetPackage(
                Argument::type('array'),
                Argument::type('string'),
                Argument::type('int'),
                Argument::type('string'),
                Argument::type('bool'),
                Argument::type(\DateTimeInterface::class),
                Argument::type(\DateTimeInterface::class)
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
        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
            Argument::type('string'),
            Argument::type(\DateTimeInterface::class)
        )->willReturn($returnArray);

        $expected['PackagesList'] = $returnArray;

        $this->quickDataCache->getBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        )->willThrow(new ResourceNotCachedException());

        $this->quickDataCache->setBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            Argument::type(GetRangeResponse::class)
        )->shouldBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(new BoxCacheMissEvent($boxId, $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)))
            ->willReturn(new \stdClass())
            ->shouldBeCalled();

        $this->availabilityHelper->buildDataForGetRange($returnArray)->willReturn($returnArray);

        $result = $this->legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $startDate);

        $this->assertInstanceOf(GetRangeResponse::class, $result);
        $this->quickDataCache->setBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $result
        )->shouldHaveBeenCalled();
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

        $this->quickDataCache->getBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        )->willReturn($cacheResult);

        $this
            ->eventDispatcher
            ->dispatch(new BoxCacheHitEvent($boxId, $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)))
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
        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
            Argument::type('string'),
            Argument::type(\DateTimeInterface::class)
        )->willReturn($returnArray);

        $expected['PackagesList'] = [];
        $this->quickDataCache->getBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        )->willThrow(new ResourceNotCachedException());

        $this->quickDataCache->setBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            Argument::type(GetRangeResponse::class)
        )->shouldBeCalled();

        $this->eventDispatcher->dispatch(
            new BoxCacheMissEvent(
                $boxId,
                $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
            )
        )->shouldBeCalled();

        $this->availabilityHelper->buildDataForGetRange($returnArray)->willReturn([]);

        $result = $this->legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $startDate);
        $this->assertInstanceOf(GetRangeResponse::class, $result);
        $this->quickDataCache->setBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $result
        )->shouldHaveBeenCalled();
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
        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
            Argument::type('string'),
            Argument::type(\DateTimeInterface::class)
        )->willReturn($returnArray);

        $expected['PackagesList'] = [];
        $exception = new \Exception();
        $this->quickDataCache->getBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        )->willThrow($exception);

        $this->quickDataCache->setBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            Argument::type(GetRangeResponse::class)
        )->shouldBeCalled();

        $this->eventDispatcher->dispatch(
            new BoxCacheErrorEvent(
                $boxId,
                $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
                $exception)
        )->shouldBeCalled();

        $this->availabilityHelper->buildDataForGetRange($returnArray)->willReturn($returnArray);

        $result = $this->legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $startDate);
        $this->assertInstanceOf(GetRangeResponse::class, $result);
        $this->quickDataCache->setBoxDate(
            $boxId,
            $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            $result
        )->shouldHaveBeenCalled();
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
        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result->reveal());

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
        $this->availabilityHelper->buildDataForGetPackage(
            Argument::type('array'),
            Argument::type('int'),
            Argument::type('string'),
            Argument::type('bool')
        )->willReturn($returnArray);

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

        $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList(
            Argument::type('array'), Argument::type(\DateTimeInterface::class)
        )->willThrow(new \Exception());

        $result = $this->prophesize(GetPackageV2Response::class);
        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result->reveal());

        $response = $this->legacyAvailabilityProvider->getAvailabilityForMultipleExperiences(
            $experienceIds,
            $dateFrom,
            $dateTo
        );

        $this->assertInstanceOf(GetPackageV2Response::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityPriceForExperience
     */
    public function testGetAvailabilityPriceForExperience()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-02');
        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result->reveal());

        $returnArray = [
            [
                'date' => '2020-06-20T00:00:00.000000',
                'stock' => '1',
                'roomStockType' => 'stock',
                'isStopSale' => '0',
                'duration' => '1',
                'price' => '86.45',
                'lastBookableDate' => null,
            ],
            [
                'date' => '2020-06-21T00:00:00.000000',
                'stock' => '1',
                'roomStockType' => 'stock',
                'isStopSale' => '0',
                'duration' => '1',
                'price' => '86.45',
                'lastBookableDate' => '2020-06-20T00:00:00.000000',
            ],
            [
                'date' => '2020-06-21T00:00:00.000000',
                'stock' => '1',
                'roomStockType' => 'stock',
                'isStopSale' => '0',
                'duration' => '1',
                'price' => '0',
                'lastBookableDate' => '2020-06-20T00:00:00.000000',
            ],
        ];

        $this->availabilityProvider
            ->getRoomAndPricesAvailabilitiesByExperienceIdAndDates('1234', $dateFrom, $dateTo)
            ->willReturn($returnArray);
        $this->availabilityProvider->getManageableComponentForGetPackage('1234')->shouldNotBeCalled();

        $this->availabilityHelper
            ->fillMissingAvailabilitiesForAvailabilityPrice(
                Argument::type('array'),
                $dateFrom,
                $dateTo,
                null
            )->willReturn($returnArray);

        $this->availabilityHelper
            ->convertAvailabilityTypeToExplicitQuickdataValue(
                Argument::type('string'),
                Argument::type('int'),
                Argument::type('string')
            )->willReturn('stock');

        $this->assertInstanceOf(
            AvailabilityPricePeriodResponse::class,
            $this->legacyAvailabilityProvider->getAvailabilityPriceForExperience('1234', $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityPriceForExperience
     */
    public function testGetAvailabilityPriceForExperienceForOnRequestWithoutAvailabilityAndPrice()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-02');
        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->serializer->fromArray(Argument::type('array'), Argument::type('string'))->willReturn($result->reveal());

        $this->availabilityProvider
            ->getRoomAndPricesAvailabilitiesByExperienceIdAndDates('1234', $dateFrom, $dateTo)
            ->willReturn([]);
        $this->availabilityProvider->getManageableComponentForGetPackage('1234')
            ->willReturn([['roomStockType' => 'on_request']]);
        $this->availabilityHelper
            ->fillMissingAvailabilitiesForAvailabilityPrice(
                Argument::type('array'),
                $dateFrom,
                $dateTo,
                Argument::type('string')
            )->willReturn([]);

        $this->availabilityHelper
            ->convertAvailabilityTypeToExplicitQuickdataValue(
                Argument::type('string'),
                Argument::type('int'),
                Argument::type('string')
            )->willReturn('stock');

        $this->assertInstanceOf(
            AvailabilityPricePeriodResponse::class,
            $this->legacyAvailabilityProvider->getAvailabilityPriceForExperience('1234', $dateFrom, $dateTo)
        );
    }
}
