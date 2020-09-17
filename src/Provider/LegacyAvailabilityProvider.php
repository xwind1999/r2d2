<?php

declare(strict_types=1);

namespace App\Provider;

use App\Cache\QuickDataCache;
use App\Contract\Response\QuickData\AvailabilityPricePeriodResponse;
use App\Contract\Response\QuickData\GetPackageResponse;
use App\Contract\Response\QuickData\GetPackageV2Response;
use App\Contract\Response\QuickData\GetRangeResponse;
use App\Contract\Response\QuickData\QuickDataErrorResponse;
use App\Contract\Response\QuickData\QuickDataResponse;
use App\Event\QuickData\BoxCacheErrorEvent;
use App\Event\QuickData\BoxCacheHitEvent;
use App\Event\QuickData\BoxCacheMissEvent;
use App\Exception\Cache\ResourceNotCachedException;
use App\Helper\AvailabilityHelper;
use App\Manager\ExperienceManager;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LegacyAvailabilityProvider
{
    protected ArrayTransformerInterface $serializer;
    protected ExperienceManager $experienceManager;
    protected AvailabilityProvider $availabilityProvider;

    private QuickDataCache $quickDataCache;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ArrayTransformerInterface $serializer,
        ExperienceManager $experienceManager,
        AvailabilityProvider $availabilityProvider,
        QuickDataCache $quickDataCache,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->serializer = $serializer;
        $this->experienceManager = $experienceManager;
        $this->availabilityProvider = $availabilityProvider;
        $this->quickDataCache = $quickDataCache;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getAvailabilityForExperience(
        string $experienceId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        $roomAvailabilities = $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdAndDates(
            $experienceId,
            $dateFrom,
            $dateTo
        );

        if (empty($roomAvailabilities)) {
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

        $returnArray = [
            'ListPrestation' => [
                AvailabilityHelper::buildDataForGetPackage(
                    AvailabilityHelper::convertToShortType(
                        array_column($roomAvailabilities, 'stock'),
                        AvailabilityHelper::getRoomStockShortType($roomAvailabilities[0]['roomStockType'])
                    ),
                    (int) $roomAvailabilities[0]['duration'],
                    $roomAvailabilities[0]['partnerGoldenId'],
                    (bool) $roomAvailabilities[0]['isSellable']
                ),
            ],
        ];

        return $this->serializer->fromArray($returnArray, GetPackageResponse::class);
    }

    public function getAvailabilitiesForBoxAndStartDate(
        string $boxId,
        \DateTimeInterface $startDate
    ): GetRangeResponse {
        try {
            $data = $this->quickDataCache->getBoxDate($boxId, $startDate->format('Y-m-d'));
            $this->eventDispatcher->dispatch(new BoxCacheHitEvent($boxId, $startDate->format('Y-m-d')));

            return $data;
        } catch (ResourceNotCachedException $exception) {
            $this->eventDispatcher->dispatch(new BoxCacheMissEvent($boxId, $startDate->format('Y-m-d')));
        } catch (\Exception $exception) {
            $this->eventDispatcher->dispatch(new BoxCacheErrorEvent($boxId, $startDate->format('Y-m-d'), $exception));
        }

        $roomAvailabilities = AvailabilityHelper::buildDataForGetRange(
            $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
                $boxId,
                $startDate)
        );
        $roomAvailabilities['PackagesList'] = $roomAvailabilities;

        $data = $this->serializer->fromArray($roomAvailabilities, GetRangeResponse::class);

        $this->quickDataCache->setBoxDate($boxId, $startDate->format('Y-m-d'), $data);

        return $data;
    }

    public function getAvailabilityForMultipleExperiences(
        array $packageCodes,
        \DateTimeInterface $startDate
    ): QuickDataResponse {
        try {
            $availabilitiesArray = [];
            $availabilities = $this->availabilityProvider->getRoomAvailabilitiesByExperienceIdsList(
                $packageCodes,
                $startDate
            );

            foreach ($availabilities as $experienceGoldenId => $availability) {
                $availabilitiesArray['ListPackage'][] = [
                    'PackageCode' => $availability['experienceId'],
                    'ListPrestation' => [
                        AvailabilityHelper::buildDataForGetPackage(
                            ['1'],
                            (int) $availability['duration'],
                            $availability['partnerId'],
                            (bool) $availability['isSellable'],
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

            if (isset($roomAvailabilityAndPrices['prices'][$date]) &&
                AvailabilityHelper::AVAILABILITY_PRICE_PERIOD_AVAILABLE === $availability['AvailabilityStatus']
            ) {
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
