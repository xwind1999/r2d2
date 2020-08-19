<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Contract\Response\QuickData\AvailabilityPricePeriodResponse;
use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Entity\Experience;
use App\Entity\Partner;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Manager\ExperienceManager;
use App\Provider\AvailabilityProvider;
use App\Provider\LegacyAvailabilityProvider;
use App\QuickData\QuickData;
use JMS\Serializer\ArrayTransformerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\Provider\LegacyAvailabilityProvider
 */
class LegacyAvailabilityProviderTest extends TestCase
{
    /**
     * @var ObjectProphecy|QuickData
     */
    protected $quickData;

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
        $this->quickData = $this->prophesize(QuickData::class);
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
        $partner->isChannelManagerActive = false;
        $partner->status = 'partner';
        $experience->partner = $partner;

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider(
            $this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([
                'duration' => 1,
                'isSellable' => true,
                'availabilities' => ['1', 'r', '1'],
            ]);

        $response = $legacyAvailabilityProvider->getAvailabilityForExperience(1234, $dateFrom, $dateTo);

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
        $partner->isChannelManagerActive = false;
        $partner->status = 'not_partner';
        $experience->partner = $partner;

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());

        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([
                'duration' => 1,
                'isSellable' => true,
                'availabilities' => ['1', 'r', '1'],
            ]);

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $response = $legacyAvailabilityProvider->getAvailabilityForExperience((int) $experience->goldenId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperienceWithConverter()
    {
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');
        $experience = new Experience();
        $experience->goldenId = '1234';
        $partner = new Partner();
        $partner->goldenId = '4321';
        $partner->isChannelManagerActive = true;
        $partner->status = 'partner';
        $experience->partner = $partner;

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([
                'duration' => 1,
                'isSellable' => true,
                'availabilities' => ['1', 'r', '1'],
            ]);

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $response = $legacyAvailabilityProvider->getAvailabilityForExperience((int) $experience->goldenId, $dateFrom, $dateTo);

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
        $partner->isChannelManagerActive = false;
        $experience->partner = $partner;

        $result = $this->prophesize(GetPackageResponse::class);

        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([
                'duration' => 1,
                'isSellable' => true,
                'availabilities' => ['1', 'r', '1'],
            ]);

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience((int) $experience->goldenId, $dateFrom, $dateTo);

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
        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $legacyAvailabilityProvider->getAvailabilityForExperience(31209470194830912, $dateFrom, $dateTo);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBox
     */
    public function testGetAvailabilitiesForBox()
    {
        $boxId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $result = $this->prophesize(GetRangeResponse::class);
        $this->quickData->getRange($boxId, $dateFrom, $dateTo)->willReturn([]);
        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilitiesForBox($boxId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetRangeResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBox
     */
    public function testGetAvailabilitiesForBoxWithConverter()
    {
        $boxId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $result = $this->prophesize(GetRangeResponse::class);
        $this->quickData->getRange($boxId, $dateFrom, $dateTo)->willReturn([
            'PackagesList' => [
                [
                    'Package' => '132982',
                    'Stock' => 0,
                    'Request' => 31,
                ],
                [
                    'Package' => '132983',
                    'Stock' => 0,
                    'Request' => 31,
                ],
            ],
        ]);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());
        $this->experienceManager->filterIdsListWithPartnerChannelManagerCondition(Argument::any(), Argument::any())->willReturn([
            '132982' => '132982',
        ]);
        $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndDates(Argument::any(), Argument::any(), Argument::any())->willReturn([
            [
                'Package' => '132984',
                'Stock' => 3,
                'Request' => 0,
            ],
            [
                'Package' => '132985',
                'Stock' => 3,
                'Request' => 0,
            ],
        ]);
        $response = $legacyAvailabilityProvider->getAvailabilitiesForBox($boxId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetRangeResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilitiesForBox
     */
    public function testGetAvailabilitiesForBoxWillFailDueToHttpError()
    {
        $boxId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $result = $this->prophesize(GetRangeResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);
        $this->quickData->getRange($boxId, $dateFrom, $dateTo)->willThrow($exception->reveal());

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilitiesForBox($boxId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetRangeResponse::class, $response);
        $this->assertEmpty($response->packagesList);
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

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $returnArray = [
            '1234' => [
                'duration' => 1,
                'isSellable' => true,
                'partnerId' => '123',
                'availabilities' => [
                    '1', '1', '1', '1', '1',
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

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
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
        $prestId = 1234;
        $experienceId = 4321;
        $experience = new Experience();
        $experience->goldenId = '4321';
        $partner = new Partner();
        $partner->goldenId = '1111';
        $partner->isChannelManagerActive = true;
        $experience->partner = $partner;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');
        $quickdataResponse = ['DaysAvailabilityPrice' => [['Date' => '2020-01-01T01:00:00.00000000+03:00']]];
        $formattedResponse = ['DaysAvailabilityPrice' => [['Date' => '2020-01-01T00:00:00.000000']]];
        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo)->willReturn($quickdataResponse);
        $this->serializer->fromArray($formattedResponse, Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityPriceForExperience($experienceId, $prestId, $dateFrom, $dateTo);

        $this->assertInstanceOf(AvailabilityPricePeriodResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityPriceForExperience
     */
    public function testGetAvailabilityPriceForExperienceWithConverter()
    {
        $prestId = 1234;
        $experienceId = 4321;
        $experience = new Experience();
        $partner = new Partner();
        $partner->isChannelManagerActive = false;
        $experience->partner = $partner;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');
        $quickdataResponse = [
            'DaysAvailabilityPrice' => [
                [
                    'Date' => '2020-01-01T01:00:00.00000000+03:00',
                    'AvailabilityValue' => 0,
                    'AvailabilityStatus' => 'Available',
                    'SellingPrice' => 0,
                    'BuyingPrice' => 0,
                ],
            ],
        ];
        $formattedResponse = [
            'DaysAvailabilityPrice' => [
                [
                    'Date' => '2020-01-01T00:00:00.000000',
                    'AvailabilityValue' => 0,
                    'AvailabilityStatus' => 'Request',
                    'SellingPrice' => 0,
                    'BuyingPrice' => 0,
                ],
            ],
        ];
        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo)->willReturn($quickdataResponse);
        $this->serializer->fromArray($formattedResponse, Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityPriceForExperience($experienceId, $prestId, $dateFrom, $dateTo);

        $this->assertInstanceOf(AvailabilityPricePeriodResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityPriceForExperience
     */
    public function testGetAvailabilityPriceForExperienceWillFailDueToHttpError()
    {
        $prestId = 1234;
        $experienceId = 4321;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityProvider->reveal()
        );

        $exception = $this->prophesize(HttpExceptionInterface::class);
        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);

        $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo)->willThrow($exception->reveal());

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityPriceForExperience($experienceId, $prestId, $dateFrom, $dateTo);

        $this->assertInstanceOf(AvailabilityPricePeriodResponse::class, $response);
        $this->assertEmpty($response->daysAvailabilityPrice);
    }
}
