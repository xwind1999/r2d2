<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Contract\Request\QuickData\GetPackageRequest;
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
}