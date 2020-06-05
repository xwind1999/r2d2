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
use App\Helper\Feature\FeatureInterface;
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

    protected FeatureInterface $availabilityConvertFlag;

    public function __construct(QuickData $quickData, ArrayTransformerInterface $serializer, ExperienceManager $experienceManager, FeatureInterface $availabilityConvertFlag)
    {
        $this->quickData = $quickData;
        $this->serializer = $serializer;
        $this->experienceManager = $experienceManager;
        $this->availabilityConvertFlag = $availabilityConvertFlag;
    }

    public function getAvailabilityForExperience(int $experienceId, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): QuickDataResponse
    {
        //we check if the experience is CM-enabled here, then we call the appropriate client
        try {
            $data = $this->quickData->getPackage($experienceId, $dateFrom, $dateTo);
            //but we process them the same way, so we have a QuickDataResponse ready
            if (isset($data['ListPrestation']['Availabilities']) && $this->availabilityConvertFlag->isEnabled()) {
                $experience = $this->experienceManager->getOneByGoldenId((string) $experienceId);
                $partner = $experience->partner;
                if (!$partner->isChannelManagerActive) {
                    $data['ListPrestation']['Availabilities'] =
                        AvailabilityHelper::convertToRequestType($data['ListPrestation']['Availabilities']);
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

            if (isset($data['PackagesList']) && $this->availabilityConvertFlag->isEnabled()) {
                $packageList = $data['PackagesList'];
                $experienceIds = array_column($packageList, 'Package');
                $inactiveChannelExperienceIds = $this->experienceManager->getIdsListWithPartnerChannelManagerInactive($experienceIds);
                foreach ($packageList as &$package) {
                    if (isset($inactiveChannelExperienceIds[$package['Package']])) {
                        $package['Request'] = (int) $package['Request'] + (int) $package['Stock'];
                        $package['Stock'] = 0;
                    }
                }
                $data['PackagesList'] = $packageList;
            }
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

            if (isset($data['ListPackage']) && $this->availabilityConvertFlag->isEnabled()) {
                $inactiveChannelExperienceIds = $this->experienceManager->getIdsListWithPartnerChannelManagerInactive($packageCodes);
                foreach ($data['ListPackage'] as &$package) {
                    if (isset($inactiveChannelExperienceIds[$package['PackageCode']])) {
                        $package['ListPrestation']['Availabilities'] =
                            AvailabilityHelper::convertToRequestType($package['ListPrestation']['Availabilities']);
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

            if (isset($data['DaysAvailabilityPrice'])) {
                if ($this->availabilityConvertFlag->isEnabled()) {
                    $experience = $this->experienceManager->getOneByGoldenId((string) $experienceId);
                    if (!$experience->partner->isChannelManagerActive) {
                        $isAvailabilityConvertNeeded = true;
                    }
                }

                foreach ($data['DaysAvailabilityPrice'] as $key => $value) {
                    $data['DaysAvailabilityPrice'][$key]['Date'] = (new \DateTime($value['Date']))
                        ->setTime(0, 0, 0, 0)
                        ->format(self::DATE_TIME_FORMAT)
                    ;

                    if ($isAvailabilityConvertNeeded) {
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
