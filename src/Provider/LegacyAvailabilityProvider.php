<?php

declare(strict_types=1);

namespace App\Provider;

use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Contract\Response\QuickData\QuickDataResponse;
use App\QuickData\QuickData;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class LegacyAvailabilityProvider
{
    protected QuickData $quickData;

    protected ArrayTransformerInterface $serializer;

    public function __construct(QuickData $quickData, ArrayTransformerInterface $serializer)
    {
        $this->quickData = $quickData;
        $this->serializer = $serializer;
    }

    public function getAvailabilityForExperience(int $experienceId, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): QuickDataResponse
    {
        //we check if the experience is CM-enabled here, then we call the appropriate client
        try {
            $data = $this->quickData->getPackage($experienceId, $dateFrom, $dateTo);
            //but we process them the same way, so we have a QuickDataResponse ready
            return $this->serializer->fromArray($data, GetPackageResponse::class);
        } catch (HttpExceptionInterface $exception) {
            $response = $this->serializer->fromArray($exception->getResponse()->toArray(false), QuickDataErrorResponse::class);
            $response->httpCode = $exception->getResponse()->getStatusCode();

            return $response;
        }
    }

    public function getAvailabilitiesForBox(int $boxId, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): GetRangeResponse
    {
        //we check if the experience is CM-enabled here, then we call the appropriate client
        try {
            $data = $this->quickData->getRange($boxId, $dateFrom, $dateTo);
            //but we process them the same way, so we have a GetRangeResponse ready
        } catch (HttpExceptionInterface $exception) {
            $data = [];
        }

        return $this->serializer->fromArray($data, GetRangeResponse::class);
    }

    public function getAvailabilityForMultipleExperiences(array $packageCodes, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): QuickDataResponse
    {
        //we check if the experience is CM-enabled here, then we call the appropriate client
        try {
            $data = $this->quickData->getPackageV2($packageCodes, $dateFrom, $dateTo);
            //but we process them the same way, so we have a GetRangeResponse ready
        } catch (HttpExceptionInterface $exception) {
            $data = [];
        }

        return $this->serializer->fromArray($data, GetPackageV2Response::class);
    }
}
