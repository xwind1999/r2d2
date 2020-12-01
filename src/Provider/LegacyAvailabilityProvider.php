<?php

declare(strict_types=1);

namespace App\Provider;

use App\Cache\QuickDataCache;
use App\Constants\DateTimeConstants;
use App\Constraint\ProductDurationUnitConstraint;
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
    private const DEFAULT_AVAILABILITY_PRICE = 0;
    private const DEFAULT_AVAILABILITY_DIVISOR = 100;

    protected ArrayTransformerInterface $serializer;
    protected ExperienceManager $experienceManager;
    protected AvailabilityProvider $availabilityProvider;
    protected AvailabilityHelper $availabilityHelper;

    private QuickDataCache $quickDataCache;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ArrayTransformerInterface $serializer,
        ExperienceManager $experienceManager,
        AvailabilityProvider $availabilityProvider,
        QuickDataCache $quickDataCache,
        EventDispatcherInterface $eventDispatcher,
        AvailabilityHelper $availabilityHelper
    ) {
        $this->serializer = $serializer;
        $this->experienceManager = $experienceManager;
        $this->availabilityProvider = $availabilityProvider;
        $this->quickDataCache = $quickDataCache;
        $this->eventDispatcher = $eventDispatcher;
        $this->availabilityHelper = $availabilityHelper;
    }

    public function getAvailabilityForExperience(
        string $experienceId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): QuickDataResponse {
        $roomAvailabilities = $this->availabilityProvider->getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
            $experienceId,
            $dateFrom,
            $dateTo
        );

        if (empty($roomAvailabilities)) {
            $component = $this->availabilityProvider->getManageableComponentForGetPackage($experienceId);

            if (empty($component)) {
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
                    $this->availabilityHelper->fillMissingAvailabilityForGetPackage(
                        [],
                        $component[0]['roomStockType'] ?: '',
                        $component[0]['duration'] ?
                            (int) $component[0]['duration'] : ProductDurationUnitConstraint::MINIMUM_DURATION,
                        $component[0]['partnerGoldenId'],
                        (bool) $component[0]['isSellable'],
                        $dateFrom,
                        $dateTo
                    ),
                ],
            ];

            return $this->serializer->fromArray($returnArray, GetPackageResponse::class);
        }

        $roomStockType = $roomAvailabilities[0]['roomStockType'];
        $duration = (int) $roomAvailabilities[0]['duration'];
        $partnerCode = (string) $roomAvailabilities[0]['partnerGoldenId'];
        $isSellable = (bool) $roomAvailabilities[0]['isSellable'];

        $sortedRoomAvailabilities = [];
        foreach ($roomAvailabilities as $availability) {
            $sortedRoomAvailabilities[$availability['date']] = $availability;
        }

        $returnArray = [
            'ListPrestation' => [
                $this->availabilityHelper->fillMissingAvailabilityForGetPackage(
                    $sortedRoomAvailabilities,
                    $roomStockType,
                    $duration,
                    $partnerCode,
                    $isSellable,
                    $dateFrom,
                    $dateTo
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
            $data = $this->quickDataCache->getBoxDate($boxId, $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
            $this->eventDispatcher->dispatch(new BoxCacheHitEvent($boxId, $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)));

            return $data;
        } catch (ResourceNotCachedException $exception) {
            $this->eventDispatcher->dispatch(new BoxCacheMissEvent($boxId, $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT)));
        } catch (\Exception $exception) {
            $this->eventDispatcher->dispatch(new BoxCacheErrorEvent($boxId, $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT), $exception));
        }

        $roomAvailabilities = $this->availabilityHelper->buildDataForGetRange(
            $this->availabilityProvider->getRoomAvailabilitiesByBoxIdAndStartDate(
                $boxId,
                $startDate)
        );
        $roomAvailabilities['PackagesList'] = $roomAvailabilities;

        $data = $this->serializer->fromArray($roomAvailabilities, GetRangeResponse::class);

        $this->quickDataCache->setBoxDate($boxId, $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT), $data);

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
                        $this->availabilityHelper->buildDataForGetPackage(
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
        $roomAndPriceAvailability = $this->availabilityProvider->getRoomAndPricesAvailabilitiesByExperienceIdAndDates(
            $experienceId,
            $dateFrom,
            $dateTo
        );

        $availabilities = [];
        $roomStockType = null;

        if (empty($roomAndPriceAvailability)) {
            $component = $this->availabilityProvider->getManageableComponentForGetPackage($experienceId);
            $roomStockType = $component[0]['roomStockType'] ?? null;
        }

        foreach ($roomAndPriceAvailability as $index => $availability) {
            /*
             * TODO: Find a way to get the currency here, and use the Money library to convert it.
             *       Maybe implementing it in the MoneyHelper?
             *       Maybe we should add the currency in the flat manageable component table?
             */
            $availability['price'] = !empty($availability['price']) ?
                ((int) $availability['price']) / self::DEFAULT_AVAILABILITY_DIVISOR : self::DEFAULT_AVAILABILITY_PRICE;
            $availability['AvailabilityValue'] = (int) $availability['stock'];
            $availability += [
                'Date' => (new \DateTime($availability['date']))->format(
                    DateTimeConstants::PRICE_PERIOD_DATE_TIME_FORMAT
                ),
                'AvailabilityStatus' => $this->availabilityHelper->convertAvailabilityTypeToExplicitQuickdataValue(
                    $availability['roomStockType'],
                    $availability['AvailabilityValue'],
                    $availability['isStopSale']
                ),
                'SellingPrice' => $availability['price'],
                'BuyingPrice' => $availability['price'],
            ];

            $availabilities[$availability['Date']] = $availability;
        }

        return $this->serializer->fromArray(
            [
                'DaysAvailabilityPrice' => $this->availabilityHelper->fillMissingAvailabilitiesForAvailabilityPrice(
                    $availabilities,
                    $dateFrom,
                    $dateTo,
                    $roomStockType
                ),
            ],
            AvailabilityPricePeriodResponse::class
        );
    }
}
