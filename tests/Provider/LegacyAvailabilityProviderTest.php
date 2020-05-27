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
use App\Helper\Feature\FeatureInterface;
use App\Manager\ExperienceManager;
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
     * @var FeatureInterface|ObjectProphecy
     */
    protected $availabilityConvertFlag;

    public function setUp(): void
    {
        $this->quickData = $this->prophesize(QuickData::class);
        $this->serializer = $this->prophesize(ArrayTransformerInterface::class);
        $this->experienceManager = $this->prophesize(ExperienceManager::class);
        $this->availabilityConvertFlag = $this->prophesize(FeatureInterface::class);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperience()
    {
        $experienceId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityConvertFlag->reveal());

        $result = $this->prophesize(GetPackageResponse::class);
        $this->quickData->getPackage($experienceId, $dateFrom, $dateTo)->willReturn([]);
        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(false);
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experienceId, $dateFrom, $dateTo);

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
        $partner->isChannelManagerActive = false;
        $experience->partner = $partner;

        $result = $this->prophesize(GetPackageResponse::class);

        $this->quickData->getPackage((int) $experience->goldenId, $dateFrom, $dateTo)->willReturn([
            'ListPrestation' => [
                'Availabilities' => [
                    ['1', 'r', '0'],
                ],
                'PrestId' => 2896684,
                'Duration' => 1,
                'LiheId' => 15257,
                'PartnerCode' => '00100901',
                'ExtraNight' => true,
                'ExtraRoom' => true,
            ],
        ]);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(true);
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityConvertFlag->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience((int) $experience->goldenId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForExperience
     */
    public function testGetAvailabilityForExperienceWillFailDueToHttpError()
    {
        $experienceId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityConvertFlag->reveal());

        $result = $this->prophesize(QuickDataErrorResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);
        $this->quickData->getPackage($experienceId, $dateFrom, $dateTo)->willThrow($exception->reveal());
        $exception->getResponse()->willReturn($responseInterface->reveal());
        $responseInterface->getStatusCode()->willReturn(405);
        $responseInterface->toArray(false)->willReturn([]);
        $this->availabilityConvertFlag->isEnabled()->willReturn(false);

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experienceId, $dateFrom, $dateTo);

        $this->assertInstanceOf(QuickDataErrorResponse::class, $response);
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
            $this->availabilityConvertFlag->reveal());

        $result = $this->prophesize(GetRangeResponse::class);
        $this->quickData->getRange($boxId, $dateFrom, $dateTo)->willReturn([]);
        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(false);
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
            $this->availabilityConvertFlag->reveal());

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
        $this->experienceManager->getIdsListWithPartnerChannelManagerInactive(Argument::any())->willReturn([
            '132982' => '132982',
        ]);
        $this->availabilityConvertFlag->isEnabled()->willReturn(true);
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
            $this->availabilityConvertFlag->reveal());

        $result = $this->prophesize(GetRangeResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);
        $this->quickData->getRange($boxId, $dateFrom, $dateTo)->willThrow($exception->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(false);

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
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityConvertFlag->reveal());

        $result = $this->prophesize(GetPackageResponse::class);
        $this->quickData->getPackageV2($experienceIds, $dateFrom, $dateTo)->willReturn([]);
        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(false);
        $response = $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForMultipleExperiences
     */
    public function testGetAvailabilityForMultipleExperiencesWithConverter()
    {
        $experienceIds = [1234, 5678];
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityConvertFlag->reveal());

        $result = $this->prophesize(GetPackageV2Response::class);
        $this->quickData->getPackageV2($experienceIds, $dateFrom, $dateTo)->willReturn([
            'ListPackage' => [
                [
                    'PackageCode' => 88826,
                    'ListPrestation' => [
                        'Availabilities' => [
                            '0', '1', 'r',
                        ],
                        'PrestId' => 2896684,
                        'Duration' => 1,
                        'LiheId' => 15257,
                        'PartnerCode' => '00100901',
                        'ExtraNight' => true,
                        'ExtraRoom' => true,
                    ],
                ],
            ],
        ]);
        $this->serializer->fromArray(Argument::any(), Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(true);
        $this->experienceManager->getIdsListWithPartnerChannelManagerInactive(Argument::any())->willReturn([
            '88826' => '88826',
            '88827' => '88827',
        ]);
        $response = $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageV2Response::class, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityForMultipleExperiences
     */
    public function testGetAvailabilityForMultipleExperiencesWillFailDueToHttpError()
    {
        $experienceIds = [1234, 5678];
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityConvertFlag->reveal());

        $exception = $this->prophesize(HttpExceptionInterface::class);
        $result = $this->prophesize(GetPackageV2Response::class);

        $this->quickData->getPackageV2($experienceIds, $dateFrom, $dateTo)->willThrow($exception->reveal());

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(false);
        $response = $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageV2Response::class, $response);
        $this->assertEmpty($response->listPackage);
    }

    /**
     * @covers ::__construct
     * @covers ::getAvailabilityPriceForExperience
     */
    public function testGetAvailabilityPriceForExperience()
    {
        $prestId = 1234;
        $experienceId = 4321;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');
        $quickdataResponse = ['DaysAvailabilityPrice' => [['Date' => '2020-01-01T01:00:00.00000000+03:00']]];
        $formattedResponse = ['DaysAvailabilityPrice' => [['Date' => '2020-01-01T00:00:00.000000']]];
        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(),
            $this->serializer->reveal(),
            $this->experienceManager->reveal(),
            $this->availabilityConvertFlag->reveal());

        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo)->willReturn($quickdataResponse);
        $this->serializer->fromArray($formattedResponse, Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(false);
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
            $this->availabilityConvertFlag->reveal());

        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->experienceManager->getOneByGoldenId(Argument::any())->willReturn($experience);
        $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo)->willReturn($quickdataResponse);
        $this->serializer->fromArray($formattedResponse, Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(true);
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
            $this->availabilityConvertFlag->reveal());

        $exception = $this->prophesize(HttpExceptionInterface::class);
        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);

        $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo)->willThrow($exception->reveal());

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $this->availabilityConvertFlag->isEnabled()->willReturn(false);
        $response = $legacyAvailabilityProvider->getAvailabilityPriceForExperience($experienceId, $prestId, $dateFrom, $dateTo);

        $this->assertInstanceOf(AvailabilityPricePeriodResponse::class, $response);
        $this->assertEmpty($response->daysAvailabilityPrice);
    }
}
