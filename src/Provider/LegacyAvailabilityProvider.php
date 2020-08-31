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
use App\QuickData\QuickData;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\HttpFoundation\Response;

class LegacyAvailabilityProvider
{
    protected QuickData $quickData;

    protected ArrayTransformerInterface $serializer;

    protected ExperienceManager $experienceManager;

    protected AvailabilityProvider $availabilityProvider;

    public function __construct(
        QuickData $quickData,
        ArrayTransformerInterface $serializer,
        ExperienceManager $experienceManager,
        AvailabilityProvider $availabilityProvider
    ) {
        $this->quickData = $quickData;
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
            $availabilitiesFromDB = $this->availabilityProvider
                ->getRoomAvailabilitiesByExperienceAndDates($experience, $dateFrom, $dateTo);

            $returnArray = [
                'ListPrestation' => [
                    AvailabilityHelper::buildDataForGetPackage(
                        AvailabilityHelper::convertToShortType($availabilitiesFromDB['availabilities']),
                        $availabilitiesFromDB['duration'],
                        $partner->goldenId,
                        $availabilitiesFromDB['isSellable']
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
                            $availability['isSellable']
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
        int $prestId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        $experience = $this->experienceManager->getOneByGoldenId($experienceId);
        $roomAvailabilityAndPrices = $this->availabilityProvider->getRoomAvailabilitiesByExperienceAndDates($experience, $dateFrom, $dateTo);

        $availabilities = [];

        foreach ($roomAvailabilityAndPrices['availabilities'] as $date => $av) {
            $availability = [
                'Date' => (new \DateTime($date))->format('Y-m-d\TH:i:s.u'),
                'AvailabilityValue' => $av['stock'],
                'AvailabilityStatus' => AvailabilityHelper::convertAvailabilityTypeToExplicitQuickdataValue($av['type'], $av['stock'], $av['isStopSale']),
                'SellingPrice' => 0,
                'BuyingPrice' => 0,
            ];

            if (isset($roomAvailabilityAndPrices['prices'][$date])) {
                $availability['SellingPrice'] = $roomAvailabilityAndPrices['prices'][$date]->price / 100;
                $availability['BuyingPrice'] = $roomAvailabilityAndPrices['prices'][$date]->price / 100;
            }

            $availabilities[] = $availability;
        }
        $data = ['DaysAvailabilityPrice' => $availabilities];

        return $this->serializer->fromArray($data, AvailabilityPricePeriodResponse::class);
    }
}
