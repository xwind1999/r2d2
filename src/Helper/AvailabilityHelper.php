<?php

declare(strict_types=1);

namespace App\Helper;

use App\Constraint\RoomStockTypeConstraint;

class AvailabilityHelper
{
    public const AVAILABILITY_PRICE_PERIOD_AVAILABLE = 'Available';

    private const AVAILABILITY_SHORTEN_INSTANT = '1';
    private const AVAILABILITY_SHORTEN_ON_REQUEST = 'r';
    private const AVAILABILITY_SHORTEN_NOT_AVAILABLE = '0';
    private const DEFAULT_DATE_TIME_FORMAT = 'Y-m-d';
    private const DEFAULT_DATE_DIFF_VALUE = 0;
    private const AVAILABILITY_PRICE_PERIOD_REQUEST = 'Request';
    private const AVAILABILITY_PRICE_PERIOD_UNAVAILABLE = 'Unavailable';

    public static function convertToShortType(array $stocksList, string $roomStockType): array
    {
        $availabilityValues = [];
        foreach ($stocksList as $stock) {
            $availabilityValues[] = (0 < (int) $stock) ? $roomStockType : self::AVAILABILITY_SHORTEN_NOT_AVAILABLE;
        }

        return $availabilityValues;
    }

    public static function getRoomStockShortType(string $roomStockType): string
    {
        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT === $roomStockType ||
            RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK === $roomStockType) {
            return self::AVAILABILITY_SHORTEN_INSTANT;
        }

        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST === $roomStockType) {
            return self::AVAILABILITY_SHORTEN_ON_REQUEST;
        }

        return self::AVAILABILITY_SHORTEN_NOT_AVAILABLE;
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

    public static function getRealStockByDate(
        array $roomAvailabilities,
        array $bookingDateStock
    ): array {
        foreach ($bookingDateStock as $booking) {
            $bookedDate = $booking['date'] instanceof \DateTime ?
                $booking['date']->format('Y-m-d') : $booking['date'];
            if (isset($roomAvailabilities[$bookedDate])) {
                $roomAvailabilities[$bookedDate]['stock'] =
                    $booking['usedStock'] > $roomAvailabilities[$bookedDate]['stock'] ?
                        self::AVAILABILITY_SHORTEN_NOT_AVAILABLE :
                        $roomAvailabilities[$bookedDate]['stock'] - $booking['usedStock'];
            }
        }

        return $roomAvailabilities;
    }

    public static function getRealStock(array $roomAvailabilities, array $bookingStockDates): array
    {
        foreach ($bookingStockDates as $booking) {
            $bookingDate = $booking['date'] instanceof \DateTime ?
                $booking['date']->format('Y-m-d') : $booking['date'];
            foreach ($roomAvailabilities as $key => $availability) {
                $availabilityDate = $availability['date'] instanceof \DateTime ?
                    $availability['date']->format('Y-m-d') : $availability['date'];
                if ($bookingDate === $availabilityDate) {
                    $roomAvailabilities[$key]['stock'] = $booking['usedStock'] > $availability['stock'] ?
                        self::AVAILABILITY_SHORTEN_NOT_AVAILABLE :
                        $availability['stock'] - $booking['usedStock'];
                }
            }
        }

        return $roomAvailabilities;
    }
}
