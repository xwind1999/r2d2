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
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class LegacyAvailabilityProvider
{
    public const PARTNER = 'partner';

    private const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.u';
    private const AVAILABILITY_INDEX = 0;
    private const QUANTITY_OF_DAYS = 1;
    private const DEFAULT_DATE_DIFF = 0;

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
        int $experienceId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        try {
            $experience = $this->experienceManager->getOneByGoldenId((string) $experienceId);
            $partner = $experience->partner;
            $availabilitiesFromDB = $this->availabilityProvider
                ->getRoomAvailabilitiesByExperienceAndDates($experience, $dateFrom, $dateTo);

            $dateDiff = $dateTo->diff($dateFrom)->days ?: self::DEFAULT_DATE_DIFF;
            // DateFrom and DateTo is the stay date, not the checkout one
            $numberOfNights = $dateDiff + self::QUANTITY_OF_DAYS;

            $returnArray = [
                'ListPrestation' => [
                    AvailabilityHelper::buildDataForGetPackage(
                        $availabilitiesFromDB['availabilities'],
                        $availabilitiesFromDB['duration'],
                        $numberOfNights,
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

    public function getAvailabilitiesForBox(
        int $boxId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): GetRangeResponse {
        //we check if the experience is CM-enabled here, then we call the appropriate client
        try {
            $data = $this->quickData->getRange($boxId, $dateFrom, $dateTo);
            //but we process them the same way, so we have a GetRangeResponse ready

            if (empty($data['PackagesList'])) {
                return $this->serializer->fromArray($data, GetRangeResponse::class);
            }

            $packageList = $data['PackagesList'];
            $availabilitiesFromQD = [];
            $experienceIds = array_column($packageList, 'Package');
            $inactiveChannelExperienceIds = $this->experienceManager
                ->filterIdsListWithPartnerChannelManagerCondition($experienceIds, false);
            foreach ($packageList as $package) {
                $experienceId = $package['Package'];
                if (!empty($inactiveChannelExperienceIds[$experienceId])) {
                    $availabilitiesFromQD[] = [
                        'Package' => $experienceId,
                        'Request' => (int) $package['Request'] + (int) $package['Stock'],
                        'Stock' => 0,
                    ];
                }
            }

            $availabilitiesFromDb = $this->availabilityProvider
                ->getRoomAvailabilitiesByBoxIdAndDates($boxId, $dateFrom, $dateTo);

            $data['PackagesList'] = array_merge($availabilitiesFromQD, $availabilitiesFromDb);
        } catch (HttpExceptionInterface $exception) {
            $data = [];
        }

        return $this->serializer->fromArray($data, GetRangeResponse::class);
    }

    public function getAvailabilityForMultipleExperiences(
        array $packageCodes,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        //we check if the experience is CM-enabled here, then we call the appropriate client
        try {
            $data = $this->quickData->getPackageV2($packageCodes, $dateFrom, $dateTo);
            //but we process them the same way, so we have a GetRangeResponse ready

            if (!empty($data['ListPackage'])) {
                $inactiveChannelExperienceIds = $this->experienceManager
                    ->filterIdsListWithPartnerChannelManagerCondition($packageCodes, false);
                foreach ($data['ListPackage'] as &$package) {
                    if (!empty($package['ListPrestation'][self::AVAILABILITY_INDEX]['Availabilities']) &&
                        !empty($inactiveChannelExperienceIds[$package['PackageCode']])
                    ) {
                        $package['ListPrestation'][self::AVAILABILITY_INDEX]['Availabilities'] =
                            AvailabilityHelper::convertToRequestType($package['ListPrestation'][self::AVAILABILITY_INDEX]['Availabilities']);
                    }
                }
            }
        } catch (HttpExceptionInterface $exception) {
            $data = [];
        }

        return $this->serializer->fromArray($data, GetPackageV2Response::class);
    }

    public function getAvailabilityPriceForExperience(
        int $experienceId,
        int $prestId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        //we check if the experience is CM-enabled here, then we call the appropriate client
        try {
            $data = $this->quickData->availabilityPricePeriod($prestId, $dateFrom, $dateTo);
            //but we process them the same way, so we have a GetRangeResponse ready

            $isAvailabilityConvertNeeded = false;

            if (!empty($data['DaysAvailabilityPrice'])) {
                $experience = $this->experienceManager->getOneByGoldenId((string) $experienceId);

                foreach ($data['DaysAvailabilityPrice'] as $key => $value) {
                    $data['DaysAvailabilityPrice'][$key]['Date'] = (new \DateTime($value['Date']))
                        ->setTime(0, 0, 0, 0)
                        ->format(self::DATE_TIME_FORMAT)
                    ;

                    if (!$experience->partner->isChannelManagerActive) {
                        $data['DaysAvailabilityPrice'][$key]['AvailabilityStatus'] =
                            AvailabilityHelper::convertAvailableValueToRequest($value['AvailabilityStatus']);
                    }
                }
            }
        } catch (HttpExceptionInterface $exception) {
            $data = [];
        }

        return $this->serializer->fromArray($data, AvailabilityPricePeriodResponse::class);
    }
}
