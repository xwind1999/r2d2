<?php

declare(strict_types=1);

namespace App\Helper;

use App\Constraint\RoomStockTypeConstraint;

class AvailabilityHelper
{
    public const AVAILABILITY_TYPE_ON_REQUEST = 'on_request';
    public const AVAILABILITY_SHORTEN_INSTANT = '1';
    public const AVAILABILITY_SHORTEN_ON_REQUEST = 'r';
    public const AVAILABILITY_SHORTEN_NOT_AVAILABLE = '0';

    private const DEFAULT_DATE_TIME_FORMAT = 'Y-m-d';
    private const DEFAULT_DATE_DIFF_VALUE = 0;
    private const AVAILABILITY_PRICE_PERIOD_AVAILABLE = 'Available';
    private const AVAILABILITY_PRICE_PERIOD_REQUEST = 'Request';
    private const AVAILABILITY_PRICE_PERIOD_UNAVAILABLE = 'Unavailable';
    private const AVAILABILITIES_DELIMITER = ',';

    public static function convertToShortType(array $availabilities): array
    {
        $shortenArray = [];

        foreach ($availabilities as $date => $availability) {
            if (0 === $availability['stock']) {
                $shortenArray[$date] = self::AVAILABILITY_SHORTEN_NOT_AVAILABLE;
            } elseif (self::AVAILABILITY_TYPE_ON_REQUEST === $availability['type']) {
                $shortenArray[$date] = self::AVAILABILITY_SHORTEN_ON_REQUEST;
            } else {
                $shortenArray[$date] = self::AVAILABILITY_SHORTEN_INSTANT;
            }
        }

        return array_values($shortenArray);
    }

    public static function fillMissingAvailabilities(
        array $availabilities,
        string $componentId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $dateFrom = new \DateTime($dateFrom->format(self::DEFAULT_DATE_TIME_FORMAT));
        $dateTo = new \DateTime($dateTo->format(self::DEFAULT_DATE_TIME_FORMAT));
        $dateDiff = $dateTo->diff($dateFrom)->days ?? self::DEFAULT_DATE_DIFF_VALUE;
        $numberOfNights = $dateDiff + 1;

        if ($numberOfNights === count($availabilities)) {
            return $availabilities;
        }

        $returnAvailabilities = [];

        $datePeriod = new \DatePeriod($dateFrom, new \DateInterval('P1D'), (clone $dateTo)->modify('+1 day'));
        foreach ($datePeriod as $date) {
            $date = $date->format('Y-m-d');
            if (!isset($availabilities[$date])) {
                $returnAvailabilities[$date] = [
                    'stock' => 0,
                    'date' => new \DateTime($date),
                    'type' => 'stock',
                    'isStopSale' => true,
                    'componentGoldenId' => $componentId,
                ];
            } else {
                $returnAvailabilities[$date] = $availabilities[$date];
            }
        }

        return $returnAvailabilities;
    }

    public static function buildDataForGetPackage(
        array $availabilities,
        int $duration,
        string $partnerId,
        bool $isSellable,
        string $partnerCurrency,
        ?string $boxCurrency
    ): array {
        $partnerCurrency = strtoupper($partnerCurrency);
        $boxCurrency = $boxCurrency ? strtoupper($boxCurrency) : '';
        $isUpsell = (true === $isSellable && $partnerCurrency === $boxCurrency);

        return [
            'Availabilities' => $availabilities,
            'PrestId' => 1,
            'Duration' => $duration,
            'LiheId' => 1,
            'PartnerCode' => $partnerId,
            'ExtraNight' => $isUpsell,
            'ExtraRoom' => $isUpsell,
        ];
    }

    public static function buildDataForGetPackageV2(
        array $availabilities,
        int $duration,
        string $partnerId,
        bool $isSellable
    ): array {
        return [
            'Availabilities' => $availabilities,
            'PrestId' => 1,
            'Duration' => $duration,
            'LiheId' => 1,
            'PartnerCode' => $partnerId,
            'ExtraNight' => $isSellable,
            'ExtraRoom' => $isSellable,
        ];
    }

    public static function buildDataForGetRange(array $availabilities): array
    {
        $returnArray = [];
        foreach ($availabilities as $availability) {
            $data = [
                'Package' => $availability['experienceGoldenId'],
                'Stock' => 0,
                'Request' => 0,
            ];
            if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST === $availability['roomStockType']) {
                $data['Request'] = 1;
            } else {
                $data['Stock'] = 1;
            }

            $returnArray[] = $data;
        }

        return $returnArray;
    }

    public static function convertAvailabilityTypeToExplicitQuickdataValue(string $type, int $stock, bool $isStopSale): string
    {
        if (true === $isStopSale) {
            return self::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE;
        }

        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK === $type && $stock > 0) {
            return self::AVAILABILITY_PRICE_PERIOD_AVAILABLE;
        }

        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST === $type) {
            return self::AVAILABILITY_PRICE_PERIOD_REQUEST;
        }

        return self::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE;
    }

    public static function mapRoomAvailabilitiesToExperience(
        array $components,
        array $roomAvailabilities,
        int $numberOfNights
    ): array {
        $returnArray = [];

        foreach ($components as $component) {
            $duration = $component['duration'] ?: 0;
            if (!empty($roomAvailabilities[$component['goldenId']]) && $duration <= $numberOfNights) {
                $returnArray[] = [
                    'Package' => $component['experienceGoldenId'],
                    'Request' => 0,
                    'Stock' => $numberOfNights,
                ];
            } else {
                $returnArray[] = [
                    'Package' => $component['experienceGoldenId'],
                    'Request' => $numberOfNights,
                    'Stock' => 0,
                ];
            }
        }

        return $returnArray;
    }
}
