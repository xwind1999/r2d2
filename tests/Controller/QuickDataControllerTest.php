<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Contract\Request\QuickData\AvailabilityPricePeriodRequest;
use App\Contract\Request\QuickData\GetPackageRequest;
use App\Contract\Request\QuickData\GetPackageV2Request;
use App\Contract\Request\QuickData\GetRangeRequest;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataResponse;
use App\Controller\QuickDataController;
use App\Provider\LegacyAvailabilityProvider;
use PHPUnit\Framework\TestCase;

class QuickDataControllerTest extends TestCase
{
    public function testGetPackage()
    {
        $experienceId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $getPackageRequest = new GetPackageRequest();
        $getPackageRequest->packageCode = $experienceId;
        $getPackageRequest->dateFrom = $dateFrom;
        $getPackageRequest->dateTo = $dateTo;
        $legacyAvailabilityProvider = $this->prophesize(LegacyAvailabilityProvider::class);

        $controller = new QuickDataController();

        $qdResponse = $this->prophesize(QuickDataResponse::class);
        $legacyAvailabilityProvider->getAvailabilityForExperience($experienceId, $dateFrom, $dateTo)->willReturn($qdResponse->reveal());
        $response = $controller->getPackage($getPackageRequest, $legacyAvailabilityProvider->reveal());

        $this->assertInstanceOf(QuickDataResponse::class, $response);
    }

    public function testGetRange()
    {
        $boxId = 1234;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $getRangeRequest = new GetRangeRequest();
        $getRangeRequest->boxVersion = $boxId;
        $getRangeRequest->dateFrom = $dateFrom;
        $getRangeRequest->dateTo = $dateTo;
        $legacyAvailabilityProvider = $this->prophesize(LegacyAvailabilityProvider::class);

        $controller = new QuickDataController();

        $qdResponse = $this->prophesize(GetRangeResponse::class);
        $legacyAvailabilityProvider->getAvailabilitiesForBox($boxId, $dateFrom, $dateTo)->willReturn($qdResponse->reveal());
        $response = $controller->getRange($getRangeRequest, $legacyAvailabilityProvider->reveal());

        $this->assertInstanceOf(GetRangeResponse::class, $response);
    }

    public function testGetPackageV2()
    {
        $experienceIds = [1234, 5678];
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $getPackageV2Request = new GetPackageV2Request();
        $getPackageV2Request->listPackageCode = $experienceIds;
        $getPackageV2Request->dateFrom = $dateFrom;
        $getPackageV2Request->dateTo = $dateTo;
        $legacyAvailabilityProvider = $this->prophesize(LegacyAvailabilityProvider::class);

        $controller = new QuickDataController();

        $qdResponse = $this->prophesize(QuickDataResponse::class);
        $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($experienceIds, $dateFrom, $dateTo)->willReturn($qdResponse->reveal());
        $response = $controller->getPackageV2($getPackageV2Request, $legacyAvailabilityProvider->reveal());

        $this->assertInstanceOf(QuickDataResponse::class, $response);
    }

    public function testAvailabilityPricePeriod()
    {
        $prestId = 5678;
        $dateFrom = new \DateTime('2020-01-01');
        $dateTo = new \DateTime('2020-01-01');

        $availabilityPricePeriodRequest = new AvailabilityPricePeriodRequest();
        $availabilityPricePeriodRequest->prestId = $prestId;
        $availabilityPricePeriodRequest->dateFrom = $dateFrom;
        $availabilityPricePeriodRequest->dateTo = $dateTo;
        $legacyAvailabilityProvider = $this->prophesize(LegacyAvailabilityProvider::class);

        $controller = new QuickDataController();

        $qdResponse = $this->prophesize(QuickDataResponse::class);
        $legacyAvailabilityProvider->getAvailabilityPriceForExperience($prestId, $dateFrom, $dateTo)->willReturn($qdResponse->reveal());
        $response = $controller->availabilityPricePeriod($availabilityPricePeriodRequest, $legacyAvailabilityProvider->reveal());

        $this->assertInstanceOf(QuickDataResponse::class, $response);
    }
}
