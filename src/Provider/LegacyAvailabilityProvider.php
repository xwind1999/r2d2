<?php

declare(strict_types=1);

namespace App\Provider;

use App\Contract\Response\QuickData\AvailabilityPricePeriodResponse;
use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Contract\Response\QuickData\QuickDataResponse;
use App\Exception\Repository\EntityNotFoundException;
use App\Helper\AvailabilityHelper;
use App\Manager\ExperienceManager;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\HttpFoundation\Response;

class LegacyAvailabilityProvider
{
    protected ArrayTransformerInterface $serializer;

    protected ExperienceManager $experienceManager;

    protected AvailabilityProvider $availabilityProvider;

    public function __construct(
        ArrayTransformerInterface $serializer,
        ExperienceManager $experienceManager,
        AvailabilityProvider $availabilityProvider
    ) {
        $this->serializer = $serializer;
        $this->experienceManager = $experienceManager;
        $this->availabilityProvider = $availabilityProvider;
    }

    public function getAvailabilityForExperience(
        string $experienceId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        try {
            $experience = $this->experienceManager->getOneByGoldenId($experienceId);
            $partner = $experience->partner;
            $availabilitiesFromDB = $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(
                $experience,
                $dateFrom,
                $dateTo
            );

            $returnArray = [
                'ListPrestation' => [
                    AvailabilityHelper::buildDataForGetPackage(
                        AvailabilityHelper::convertToShortType($availabilitiesFromDB['availabilities']),
                        $availabilitiesFromDB['duration'],
                        $partner->goldenId,
                        $availabilitiesFromDB['isSellable'],
                    ),
                ],
            ];
        } catch (EntityNotFoundException $exception) {
            $response = $this->serializer->fromArray(
                [
                    'ResponseStatus' => [
                        'ErrorCode' => 'ArgumentException',
                        'Message' => 'Invalid Request',
                        'StackTrace' => '',
                        'Errors' => [],
                    ],
                ],
                QuickDataErrorResponse::class
            );
            $response->httpCode = Response::HTTP_BAD_REQUEST;

            return $response;
        }

        return $this->serializer->fromArray($returnArray, GetPackageResponse::class);
    }

    public function getAvailabilitiesForBoxAndStartDate(
        string $boxId,
        \DateTimeInterface $startDate
    ): GetRangeResponse {
        $roomAvailabilities = AvailabilityHelper::buildDataForGetRange(
            $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
                $boxId,
                $startDate)
        );
        $roomAvailabilities['PackagesList'] = $roomAvailabilities;

        return $this->serializer->fromArray($roomAvailabilities, GetRangeResponse::class);
    }

    public function getAvailabilityForMultipleExperiences(
        array $packageCodes,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        try {
            $availabilitiesArray = [];
            $availabilities = $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList(
                $packageCodes,
                $dateFrom,
                $dateTo
            );

            foreach ($availabilities as $componentId => $availability) {
                $availabilitiesArray['ListPackage'][] = [
                    'PackageCode' => $availability['experienceId'],
                    'ListPrestation' => [
                        AvailabilityHelper::buildDataForGetPackage(
                            AvailabilityHelper::convertToShortType($availability['availabilities']),
                            $availability['duration'],
                            $availability['partnerId'],
                            $availability['isSellable'],
                        ),
                    ],
                ];
            }
        } catch (\Exception $exception) {
            $availabilitiesArray = [];
        }

        return $this->serializer->fromArray($availabilitiesArray, GetPackageV2Response::class);
    }

    public function getAvailabilityPriceForExperience(
        string $experienceId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        $experience = $this->experienceManager->getOneByGoldenId($experienceId);
        $roomAvailabilityAndPrices = $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates(
            $experience,
            $dateFrom,
            $dateTo
        );

        $availabilities = [];
        foreach ($roomAvailabilityAndPrices['availabilities'] as $date => $availability) {
            $availability = [
                'Date' => (new \DateTime($date))->format('Y-m-d\TH:i:s.u'),
                'AvailabilityValue' => $availability['stock'],
                'AvailabilityStatus' => AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue(
                    $availability['type'],
                    $availability['stock'],
                    $availability['isStopSale']
                ),
                'SellingPrice' => 0,
                'BuyingPrice' => 0,
            ];

            if (isset($roomAvailabilityAndPrices['prices'][$date])) {
                $availability['SellingPrice'] = $roomAvailabilityAndPrices['prices'][$date]->price / 100;
                $availability['BuyingPrice'] = $roomAvailabilityAndPrices['prices'][$date]->price / 100;
            }

            $availabilities[] = $availability;
        }

        return $this->serializer->fromArray(
            ['DaysAvailabilityPrice' => $availabilities],
            AvailabilityPricePeriodResponse::class
        );
    }
}
