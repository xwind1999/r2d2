<?php

declare(strict_types=1);

namespace App\Tests\Provider;

use App\Contract\Response\QuickData\AvailabilityPricePeriodResponse;
use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Provider\LegacyAvailabilityProvider;
use App\QuickData\QuickData;
use JMS\Serializer\ArrayTransformerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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

    public function setUp(): void
    {
        $this->quickData = $this->prophesize(QuickData::class);
        $this->serializer = $this->prophesize(ArrayTransformerInterface::class);
    }

    public function testGetAvailabilityForExperience()
    {
        $experienceId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $result = $this->prophesize(GetPackageResponse::class);
        $this->quickData->getPackage($experienceId, $dateFrom, $dateTo)->willReturn([]);
        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experienceId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    public function testGetAvailabilityForExperienceWillFailDueToHttpError()
    {
        $experienceId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $result = $this->prophesize(QuickDataErrorResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);
        $this->quickData->getPackage($experienceId, $dateFrom, $dateTo)->willThrow($exception->reveal());
        $exception->getResponse()->willReturn($responseInterface->reveal());
        $responseInterface->getStatusCode()->willReturn(405);
        $responseInterface->toArray(false)->willReturn([]);

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityForExperience($experienceId, $dateFrom, $dateTo);

        $this->assertInstanceOf(QuickDataErrorResponse::class, $response);
    }

    public function testGetAvailabilitiesForBox()
    {
        $boxId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $result = $this->prophesize(GetRangeResponse::class);
        $this->quickData->getRange($boxId, $dateFrom, $dateTo)->willReturn([]);
        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilitiesForBox($boxId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetRangeResponse::class, $response);
    }

    public function testGetAvailabilitiesForBoxWillFailDueToHttpError()
    {
        $boxId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $result = $this->prophesize(GetRangeResponse::class);
        $responseInterface = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(HttpExceptionInterface::class);
        $this->quickData->getRange($boxId, $dateFrom, $dateTo)->willThrow($exception->reveal());

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilitiesForBox($boxId, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetRangeResponse::class, $response);
        $this->assertEmpty($response->packagesList);
    }

    public function testGetAvailabilityForMultipleExperiences()
    {
        $experienceIds = [1234, 5678];
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $result = $this->prophesize(GetPackageResponse::class);
        $this->quickData->getPackageV2($experienceIds, $dateFrom, $dateTo)->willReturn([]);
        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageResponse::class, $response);
    }

    public function testGetAvailabilityForMultipleExperiencesWillFailDueToHttpError()
    {
        $experienceIds = [1234, 5678];
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $exception = $this->prophesize(HttpExceptionInterface::class);
        $result = $this->prophesize(GetPackageV2Response::class);

        $this->quickData->getPackageV2($experienceIds, $dateFrom, $dateTo)->willThrow($exception->reveal());

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo);

        $this->assertInstanceOf(GetPackageV2Response::class, $response);
        $this->assertEmpty($response->listPackage);
    }

    public function testGetAvailabilityPriceForExperience()
    {
        $prestId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');
        $quickdataResponse = ['DaysAvailabilityPrice' => [['Date' => '2020-01-01T01:00:00.00000000+03:00']]];
        $formattedResponse = ['DaysAvailabilityPrice' => [['Date' => '2020-01-01T00:00:00.000000']]];
        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);
        $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo)->willReturn($quickdataResponse);
        $this->serializer->fromArray($formattedResponse, Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityPriceForExperience($prestId, $dateFrom, $dateTo);

        $this->assertInstanceOf(AvailabilityPricePeriodResponse::class, $response);
    }

    public function testGetAvailabilityPriceForExperienceWillFailDueToHttpError()
    {
        $prestId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $legacyAvailabilityProvider = new LegacyAvailabilityProvider($this->quickData->reveal(), $this->serializer->reveal());

        $exception = $this->prophesize(HttpExceptionInterface::class);
        $result = $this->prophesize(AvailabilityPricePeriodResponse::class);

        $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo)->willThrow($exception->reveal());

        $this->serializer->fromArray([], Argument::any())->willReturn($result->reveal());
        $response = $legacyAvailabilityProvider->getAvailabilityPriceForExperience($prestId, $dateFrom, $dateTo);

        $this->assertInstanceOf(AvailabilityPricePeriodResponse::class, $response);
        $this->assertEmpty($response->daysAvailabilityPrice);
    }
}
