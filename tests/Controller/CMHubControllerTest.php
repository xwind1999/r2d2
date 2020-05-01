<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Contract\Request\CMHub\GetAvailabilityRequest;
use App\Contract\Response\CMHub\CMHubResponse;
use App\Controller\CMHubController;
use App\Provider\AvailabilityProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Controller\CMHubController
 */
class CMHubControllerTest extends TestCase
{
    /**
     * @covers ::getAvailability
     */
    public function testGetAvailability()
    {
        $productId = 286201;
        $dateFrom = new \DateTime('2020-04-04');
        $dateTo = new \DateTime('2020-04-06');

        $getPackageRequest = new GetAvailabilityRequest();
        $getPackageRequest->productId = $productId;
        $getPackageRequest->start = $dateFrom;
        $getPackageRequest->end = $dateTo;
        $availabilityProvider = $this->prophesize(AvailabilityProvider::class);

        $controller = new CMHubController();

        $qdResponse = $this->prophesize(CMHubResponse::class);
        $availabilityProvider->getAvailability($productId, $dateFrom, $dateTo)->willReturn($qdResponse->reveal());
        $response = $controller->getAvailability($getPackageRequest, $availabilityProvider->reveal());

        $this->assertInstanceOf(CMHubResponse::class, $response);
    }
}
