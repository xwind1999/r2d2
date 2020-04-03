<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\QuickData\GetPackageRequest;
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
}
