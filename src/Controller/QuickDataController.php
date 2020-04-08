<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\QuickData\AvailabilityPricePeriodRequest;
use App\Contract\Request\QuickData\GetPackageRequest;
use App\Contract\Request\QuickData\GetPackageV2Request;
use App\Contract\Request\QuickData\GetRangeRequest;
use App\Contract\Response\QuickData\QuickDataResponse;
use App\Provider\LegacyAvailabilityProvider;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

class QuickDataController
{
    /**
     * @Route("/api/quickdata/GetPackage/1/{engineId}", methods={"GET"}, format="json")
     *
     * @SWG\Parameter(
     *     name="engineId",
     *     in="path",
     *     type="string",
     *     format="string",
     *     description="Ignored"
     * )
     * @SWG\Parameter(
     *     name="PackageCode",
     *     in="query",
     *     type="integer",
     *     format="integer",
     *     description="Experience ID (example: 88826)"
     * )
     * @SWG\Parameter(
     *     name="dateFrom",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Parameter(
     *     name="dateTo",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Tag(name="quickdata")
     * @SWG\Response(
     *     description="Quickdata handled",
     *     response="200"
     * )
     */
    public function getPackage(GetPackageRequest $getPackageRequest, LegacyAvailabilityProvider $legacyAvailabilityProvider): QuickDataResponse
    {
        $quickDataResponse = $legacyAvailabilityProvider->getAvailabilityForExperience($getPackageRequest->packageCode, $getPackageRequest->dateFrom, $getPackageRequest->dateTo);

        return $quickDataResponse;
    }

    /**
     * @Route("/api/quickdata/GetRangeV2/1/{engineId}", methods={"GET"}, format="json")
     *
     * @SWG\Parameter(
     *     name="engineId",
     *     in="path",
     *     type="string",
     *     format="string",
     *     description="Ignored"
     * )
     * @SWG\Parameter(
     *     name="boxVersion",
     *     in="query",
     *     type="integer",
     *     format="integer",
     *     description="Box ID (example: 523950)"
     * )
     * @SWG\Parameter(
     *     name="dateFrom",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Parameter(
     *     name="dateTo",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Tag(name="quickdata")
     * @SWG\Response(
     *     description="Quickdata handled",
     *     response="200"
     * )
     */
    public function getRange(GetRangeRequest $getRangeRequest, LegacyAvailabilityProvider $legacyAvailabilityProvider): QuickDataResponse
    {
        $quickDataResponse = $legacyAvailabilityProvider->getAvailabilitiesForBox($getRangeRequest->boxVersion, $getRangeRequest->dateFrom, $getRangeRequest->dateTo);

        return $quickDataResponse;
    }

    /**
     * @Route("/api/quickdata/GetPackageV2/1/{engineId}", methods={"GET"}, format="json")
     *
     * @SWG\Parameter(
     *     name="engineId",
     *     in="path",
     *     type="string",
     *     format="string",
     *     description="Ignored"
     * )
     * @SWG\Parameter(
     *     name="ListPackageCode",
     *     in="query",
     *     type="string",
     *     format="string",
     *     description="Experience IDs, comma separated (example: 88826,677507)"
     * )
     * @SWG\Parameter(
     *     name="dateFrom",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Parameter(
     *     name="dateTo",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Tag(name="quickdata")
     * @SWG\Response(
     *     description="Quickdata handled",
     *     response="200"
     * )
     */
    public function getPackageV2(GetPackageV2Request $getPackageV2Request, LegacyAvailabilityProvider $legacyAvailabilityProvider): QuickDataResponse
    {
        $quickDataResponse = $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences($getPackageV2Request->listPackageCode, $getPackageV2Request->dateFrom, $getPackageV2Request->dateTo);

        return $quickDataResponse;
    }

    /**
     * @Route("/api/quickdata/availabilitypriceperiod/1/{engineId}", methods={"GET"}, format="json")
     *
     * @SWG\Parameter(
     *     name="engineId",
     *     in="path",
     *     type="string",
     *     format="string",
     *     description="Ignored"
     * )
     * @SWG\Parameter(
     *     name="prestid",
     *     in="query",
     *     type="integer",
     *     format="integer",
     *     description="Prest ID (example: 2878007)"
     * )
     * @SWG\Parameter(
     *     name="datefrom",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Parameter(
     *     name="dateto",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Tag(name="quickdata")
     * @SWG\Response(
     *     description="Quickdata handled",
     *     response="200"
     * )
     */
    public function availabilityPricePeriod(AvailabilityPricePeriodRequest $availabilityPricePeriodRequest, LegacyAvailabilityProvider $legacyAvailabilityProvider): QuickDataResponse
    {
        $quickDataResponse = $legacyAvailabilityProvider->getAvailabilityPriceForExperience($availabilityPricePeriodRequest->prestId, $availabilityPricePeriodRequest->dateFrom, $availabilityPricePeriodRequest->dateTo);

        return $quickDataResponse;
    }
}
