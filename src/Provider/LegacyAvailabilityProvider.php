<?php

declare(strict_types=1);

namespace App\Provider;

use App\Contract\Response\QuickData\AvailabilityPricePeriodResponse;
use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Contract\Response\QuickData\QuickDataResponse;
use App\Helper\AvailabilityHelper;
use App\Manager\ExperienceManager;
use App\QuickData\QuickData;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class LegacyAvailabilityProvider
{
    private const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.u';

    protected QuickData $quickData;

    protected ArrayTransformerInterface $serializer;

    protected ExperienceManager $experienceManager;

    protected AvailabilityProvider $availabilityProvider;

    public function __construct(QuickData $quickData, ArrayTransformerInterface $serializer, ExperienceManager $experienceManager, AvailabilityProvider $availabilityProvider)
    {
        $this->quickData = $quickData;
        $this->serializer = $serializer;
        $this->experienceManager = $experienceManager;
        $this->availabilityProvider = $availabilityProvider;
    }

    public function getAvailabilityForExperience(int $experienceId, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): QuickDataResponse
    {
        //we check if the experience is CM-enabled here, then we call the appropriate client
        try {
            $data = $this->quickData->getPackage($experienceId, $dateFrom, $dateTo);
            //but we process them the same way, so we have a QuickDataResponse ready

            if (!empty($data['ListPrestation'][0]['Availabilities'])) {
                $experience = $this->experienceManager->getOneByGoldenId((string) $experienceId);
                $partner = $experience->partner;
                if (!$partner->isChannelManagerActive) {
                    $data['ListPrestation'][0]['Availabilities'] =
                        AvailabilityHelper::convertToRequestType($data['ListPrestation'][0]['Availabilities']);
                }
            }

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

            if (empty($data['PackagesList'])) {
                return $this->serializer->fromArray($data, GetRangeResponse::class);
            }

            $packageList = $data['PackagesList'];
            $availabilitiesFromQD = [];
            $experienceIds = array_column($packageList, 'Package');
            $inactiveChannelExperienceIds = $this->experienceManager->filterIdsListWithPartnerChannelManagerCondition($experienceIds, false);
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

            $availabilitiesFromDb = $this->availabilityProvider->getRoomAvailabilities($boxId, $dateFrom, $dateTo);

            $data['PackagesList'] = array_merge($availabilitiesFromQD, $availabilitiesFromDb);
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

            if (!empty($data['ListPackage'])) {
                $inactiveChannelExperienceIds = $this->experienceManager->filterIdsListWithPartnerChannelManagerCondition($packageCodes, false);
                foreach ($data['ListPackage'] as &$package) {
                    if (!empty($package['ListPrestation'][0]['Availabilities']) && !empty($inactiveChannelExperienceIds[$package['PackageCode']])) {
                        $package['ListPrestation'][0]['Availabilities'] = AvailabilityHelper::convertToRequestType($package['ListPrestation'][0]['Availabilities']);
                    }
                }
            }
        } catch (HttpExceptionInterface $exception) {
            $data = [];
        }

        return $this->serializer->fromArray($data, GetPackageV2Response::class);
    }

    public function getAvailabilityPriceForExperience(int $experienceId, int $prestId, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): QuickDataResponse
    {
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
                        $data['DaysAvailabilityPrice'][$key]['AvailabilityStatus'] = AvailabilityHelper::convertAvailableValueToRequest($value['AvailabilityStatus']);
                    }
                }
            }
        } catch (HttpExceptionInterface $exception) {
            $data = [];
        }

        return $this->serializer->fromArray($data, AvailabilityPricePeriodResponse::class);
    }
}
