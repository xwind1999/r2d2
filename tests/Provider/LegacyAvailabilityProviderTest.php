<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Contract\Response\QuickData\AvailabilityPricePeriodResponse;
use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Entity\Box;
use App\Entity\Experience;
use App\Entity\Partner;
use App\Entity\RoomPrice;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\ExperienceManager;
use App\Provider\AvailabilityProvider;
use App\Provider\LegacyAvailabilityProvider;
use JMS\Serializer\ArrayTransformerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

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

    public function setUp(): void
    {
        $this->serializer = $this->prophesize(ArrayTransformerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->availabilityProvider = $this->prophesize(AvailabilityProvider::class);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperience()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');
        $experience = new Experience();
        $experience->goldenId = '1234';
        $partner = new Partner();
        $partner->goldenId = '4321';
        $partner->status = 'partner';
        $partner->currency = 'EUR';
        $experience->partner = $partner;
        $box = new Box();
        $box->currency = 'EUR';

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([
                'duration' => 1,
                'isSellable' => true,
                'experienceId' => '1234',
                'availabilities' => [
                    '2020-01-01' => ['stock' => 1, 'type' => 'stock'],
                    '2020-01-02' => ['stock' => 1, 'type' => 'on_request'],
                    '2020-01-03' => ['stock' => 1, 'type' => 'stock'],
                ],
                'box' => $box,
                'partner' => $partner,
            ]);

        $response = $legacyAvailabilityProvider->getAvailabilityForExperience('1234', $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperienceForOtherPartnerType()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');
        $experience = new Experience();
        $experience->goldenId = '1234';
        $partner = new Partner();
        $partner->goldenId = '4321';
        $partner->status = 'not_partner';
        $partner->currency = 'EUR';
        $experience->partner = $partner;
        $box = new Box();
        $box->currency = 'EUR';

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([
                'duration' => 1,
                'isSellable' => true,
                'experienceId' => '1234',
                'availabilities' => [
                    '2020-01-01' => ['stock' => 1, 'type' => 'stock'],
                    '2020-01-02' => ['stock' => 1, 'type' => 'on_request'],
                    '2020-01-03' => ['stock' => 1, 'type' => 'stock'],
                ],
                'box' => $box,
                'partner' => $partner,
            ]);

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experience->goldenId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperienceWithConverter()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-03');
        $experience = new Experience();
        $experience->goldenId = '1234';
        $partner = new Partner();
        $partner->goldenId = '4321';
        $partner->status = 'partner';
        $partner->currency = 'EUR';
        $experience->partner = $partner;
        $box = new Box();
        $box->currency = 'EUR';

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([
                'duration' => 1,
                'isSellable' => true,
                'experienceId' => '4321',
                'availabilities' => [
                    '2020-01-01' => [
                        'stock' => 1,
                        'type' => 'stock',
                    ],
                    '2020-01-02' => [
                        'stock' => 1,
                        'type' => 'on_request',
                    ],
                    '2020-01-03' => [
                        'stock' => 1,
                        'type' => 'stock',
                    ],
                ],
                'box' => $box,
                'partner' => $partner,
            ]);

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experience->goldenId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperienceWithConverterWrongFormat()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');
        $experience = new Experience();
        $experience->goldenId = '1234';
        $partner = new Partner();
        $partner->goldenId = '4321';
        $partner->status = 'partner';
        $partner->currency = 'EUR';
        $experience->partner = $partner;
        $box = new Box();
        $box->currency = 'EUR';

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([
                'duration' => 1,
                'isSellable' => true,
                'experienceId' => '4321',
                'availabilities' => [
                    '2020-01-01' => ['stock' => 1, 'type' => 'stock'],
                    '2020-01-02' => ['stock' => 1, 'type' => 'on_request'],
                    '2020-01-03' => ['stock' => 1, 'type' => 'stock'],
                ],
                'box' => $box,
                'partner' => $partner,
            ]);

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experience->goldenId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperienceWithNoExperienceExist()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $result = $this->prophesize(QuickDataErrorResponse::class);
        $result->httpCode = 400;

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->experienceManager->getOneByGoldenId(Argument::any())
            ->shouldBeCalledOnce()
            ->willThrow(ExperienceNotFoundException::class);
        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $legacyAvailabilityProvider->getAvailabilityForExperience('31209470194830912', $dateFrom, $dateTo);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBoxAndStartDate
     */
    public function testGetAvailabilitiesForBox()
    {
        $boxId = '1234';
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-05');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

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

        $this->assertInstanceOf(
            GetRangeResponse::class,
            $legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBoxAndStartDate
     */
    public function testGetAvailabilitiesForBoxWillFail()
    {
        $boxId = '1234';
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-05');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );
        $returnArray = [];

        $result = $this->prophesize(GetRangeResponse::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn($returnArray);

        $expected['PackagesList'] = [];

        $this->assertInstanceOf(
            GetRangeResponse::class,
            $legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate($boxId, $dateFrom, $dateTo)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForMultipleExperiences
     */
    public function testGetAvailabilityForMultipleExperiences()
    {
        $experienceIds = [1234, 5678];
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-05');

        $result = $this->prophesize(GetPackageV2Response::class);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

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

        $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList($experienceIds, $dateFrom, $dateTo)
            ->willReturn($returnArray);
        $response = $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo);

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

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $response = $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageV2Response::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityPriceForExperience
     */
    public function testGetAvailabilityPriceForExperience()
    {
        $experienceId = '4321';
        $experience = new Experience();
        $experience->goldenId = '4321';
        $partner = new Partner();
        $partner->goldenId = '1111';
        $partner->currency = 'EUR';
        $experience->partner = $partner;
        $box = new Box();
        $box->currency = 'EUR';
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-02');
        $formattedResponse = [
            'DaysAvailabilityPrice' => [
                [
                    'Date' => '2020-01-01T00:00:00.000000',
                    'AvailabilityValue' => 1,
                    'AvailabilityStatus' => 'Available',
                    'BuyingPrice' => 0.05,
                    'SellingPrice' => 0.05,
                ],
                [
                    'Date' => '2020-01-02T00:00:00.000000',
                    'AvailabilityValue' => 1,
                    'AvailabilityStatus' => 'Available',
                    'BuyingPrice' => 0.1,
                    'SellingPrice' => 0.1,
                ],
            ],
        ];
        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->serializer->fromArray($formattedResponse, Argument::any())->willReturn($result->reveal());

        $this->availabilityProvider
            ->getRoomAvailabilitiesByExperienceAndDates($experience, $dateFrom, $dateTo)
            ->willReturn([
                'duration' => 1,
                'isSellable' => 1,
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
                ],
                'box' => $box,
                'partner' => $partner,
                'prices' => [
                    '2020-01-01' => (function () {
                        $roomPrice = new RoomPrice();
                        $roomPrice->price = 5;

                        return $roomPrice;
                    })(),
                    '2020-01-02' => (function () {
                        $roomPrice = new RoomPrice();
                        $roomPrice->price = 10;

                        return $roomPrice;
                    })(),
                ],
            ]);

        $response = $legacyAvailabilityProvider->getAvailabilityPriceForExperience($experienceId, $dateFrom, $dateTo);

        $this->assertInstanceOf(AvailabilityPricePeriodResponse::class, $response);
    }
}
