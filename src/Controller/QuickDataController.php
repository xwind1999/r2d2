<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\QuickData\AvailabilityPricePeriodRequest;
use App\Contract\Request\QuickData\GetPackageRequest;
use App\Contract\Request\QuickData\GetPackageV2Request;
use App\Contract\Request\QuickData\GetRangeRequest;
use App\Contract\Response\QuickData\QuickDataResponse;
use App\Provider\LegacyAvailabilityProvider;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

class QuickDataController
{
    /**
     * @Route("/quickdata/GetPackage/1/{engineId}", methods={"GET"}, format="json")
     *
     * @OA\Parameter(
     *     name="engineId",
     *     in="path",
     *     description="Ignored",
     *     @OA\Schema(
     *         type="string"
     *     )
     * )
     * @OA\Parameter(
     *     name="PackageCode",
     *     in="query",
     *     description="Experience ID (example: 88826)",
     *     @OA\Schema(
     *         type="integer",
     *         format="int64"
     *     )
     * )
     * @OA\Parameter(
     *     name="dateFrom",
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *         format="date"
     *     )
     * )
     * @OA\Parameter(
     *     name="dateTo",
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *         format="date"
     *     )
     * )
     * @OA\Tag(name="quickdata")
     * @OA\Response(
     *     description="Quickdata handled",
     *     response="200"
     * )
     */
    public function getPackage(
        GetPackageRequest $getPackageRequest,
        LegacyAvailabilityProvider $legacyAvailabilityProvider
    ): QuickDataResponse {
        return $legacyAvailabilityProvider->getAvailabilityForExperience(
            $getPackageRequest->packageCode,
            $getPackageRequest->dateFrom,
            $getPackageRequest->dateTo
        );
    }

    /**
     * @Route("/quickdata/GetRangeV2/1/{engineId}", methods={"GET"}, format="json")
     *
     * @OA\Parameter(
     *     name="engineId",
     *     in="path",
     *     description="Ignored",
     *     @OA\Schema(
     *         type="string"
     *     )
     * )
     * @OA\Parameter(
     *     name="boxVersion",
     *     in="query",
     *     description="Box ID (example: 523950)",
     *     @OA\Schema(
     *         type="integer",
     *         format="int64"
     *     )
     * )
     * @OA\Parameter(
     *     name="dateFrom",
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *         format="date"
     *     )
     * )
     * @OA\Parameter(
     *     name="dateTo",
     *     in="query",
     *     description="Ignored",
     *     @OA\Schema(
     *         type="string",
     *         format="date"
     *     )
     * )
     * @OA\Tag(name="quickdata")
     * @OA\Response(
     *     description="Quickdata handled",
     *     response="200"
     * )
     */
    public function getRange(
        GetRangeRequest $getRangeRequest,
        LegacyAvailabilityProvider $legacyAvailabilityProvider
    ): QuickDataResponse {
        return $legacyAvailabilityProvider->getAvailabilitiesForBoxAndStartDate(
            $getRangeRequest->boxVersion,
            $getRangeRequest->dateFrom
        );
    }

    /**
     * @Route("/quickdata/GetPackageV2/1/{engineId}", methods={"GET"}, format="json")
     *
     * @OA\Parameter(
     *     name="engineId",
     *     in="path",
     *     description="Ignored",
     *     @OA\Schema(
     *         type="string"
     *     )
     * )
     * @OA\Parameter(
     *     name="ListPackageCode",
     *     in="query",
     *     description="Experience IDs, comma separated (example: 88826,677507)",
     *     @OA\Schema(
     *         type="string"
     *     )
     * )
     * @OA\Parameter(
     *     name="dateFrom",
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *         format="date"
     *     )
     * )
     * @OA\Parameter(
     *     name="dateTo",
     *     in="query",
     *     description="Ignored",
     *     @OA\Schema(
     *         type="string",
     *         format="date"
     *     )
     * )
     * @OA\Tag(name="quickdata")
     * @OA\Response(
     *     description="Quickdata handled",
     *     response="200"
     * )
     */
    public function getPackageV2(
        GetPackageV2Request $getPackageV2Request,
        LegacyAvailabilityProvider $legacyAvailabilityProvider
    ): QuickDataResponse {
        return $legacyAvailabilityProvider->getAvailabilityForMultipleExperiences(
            $getPackageV2Request->listPackageCode,
            $getPackageV2Request->dateFrom
        );
    }

    /**
     * @Route("/quickdata/availabilitypriceperiod/1/{engineId}", methods={"GET"}, format="json")
     *
     * @OA\Parameter(
     *     name="engineId",
     *     in="path",
     *     description="Ignored",
     *     @OA\Schema(
     *         type="string"
     *     )
     * )
     * @OA\Parameter(
     *     name="ExperienceId",
     *     in="query",
     *     description="Experience ID (example: 88826)",
     *     @OA\Schema(
     *         type="integer",
     *         format="int64"
     *     )
     * )
     * @OA\Parameter(
     *     name="prestid",
     *     in="query",
     *     description="Prest ID (example: 2878007)",
     *     @OA\Schema(
     *         type="integer",
     *         format="int64"
     *     )
     * )
     * @OA\Parameter(
     *     name="datefrom",
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *         format="date"
     *     )
     * )
     * @OA\Parameter(
     *     name="dateto",
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *         format="date"
     *     )
     * )
     * @OA\Tag(name="quickdata")
     * @OA\Response(
     *     description="Quickdata handled",
     *     response="200"
     * )
     */
    public function availabilityPricePeriod(
        AvailabilityPricePeriodRequest $availabilityPricePeriodRequest,
        LegacyAvailabilityProvider $legacyAvailabilityProvider
    ): QuickDataResponse {
        return $legacyAvailabilityProvider->getAvailabilityPriceForExperience(
            $availabilityPricePeriodRequest->experienceId,
            $availabilityPricePeriodRequest->dateFrom,
            $availabilityPricePeriodRequest->dateTo
        );
    }
}
