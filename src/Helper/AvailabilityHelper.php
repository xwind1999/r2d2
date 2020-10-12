<?php

declare(strict_types=1);

namespace App\Helper;

use App\Constraint\RoomStockTypeConstraint;
use App\Exception\Helper\InvalidDatesForPeriod;

class AvailabilityHelper
{
    public const AVAILABILITY_PRICE_PERIOD_AVAILABLE = 'Available';
    public const PRICE_PERIOD_DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.u';

    private const AVAILABILITY_SHORTEN_INSTANT = '1';
    private const AVAILABILITY_SHORTEN_ON_REQUEST = 'r';
    private const AVAILABILITY_SHORTEN_NOT_AVAILABLE = '0';
    private const DEFAULT_DATE_FORMAT = 'Y-m-d';
    private const DEFAULT_DATE_DIFF_VALUE = 0;
    private const AVAILABILITY_PRICE_PERIOD_REQUEST = 'Request';
    private const AVAILABILITY_PRICE_PERIOD_UNAVAILABLE = 'Unavailable';
    private const DEFAULT_TIME_FORMAT = 'T00:00:00.000000';

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

    public static function fillMissingAvailabilitiesForAvailabilityPrice(
        array $availabilities,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $isAvailabilityMissing = self::validateMissingAvailability($availabilities, $dateFrom, $dateTo);
        if (false === $isAvailabilityMissing) {
            return $availabilities;
        }
        $returnAvailabilities = [];
        $datePeriod = new \DatePeriod(
            (new \DateTime($dateFrom->format(self::DEFAULT_DATE_FORMAT))),
            new \DateInterval('P1D'),
            (new \DateTime($dateTo->format(self::DEFAULT_DATE_FORMAT)))->modify('+1 day')
        );
        foreach ($datePeriod as $date) {
            $date = $date->format(self::DEFAULT_DATE_FORMAT).self:: DEFAULT_TIME_FORMAT;
            if (!isset($availabilities[$date])) {
                $returnAvailabilities[$date] = [
                    'Date' => $date,
                    'AvailabilityValue' => 0,
                    'AvailabilityStatus' => self::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                    'SellingPrice' => 0.00,
                    'BuyingPrice' => 0.00,
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

    private static function validateMissingAvailability(
        array $availabilities,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): bool {
        $dateFrom = new \DateTime($dateFrom->format(self::DEFAULT_DATE_FORMAT));
        $dateTo = new \DateTime($dateTo->format(self::DEFAULT_DATE_FORMAT));
        $numberOfNights = ($dateTo->diff($dateFrom)->days ?: self::DEFAULT_DATE_DIFF_VALUE) + 1;

        return $numberOfNights !== count($availabilities);
    }

    public static function fillMissingAvailabilityForGetPackage(
        array $availabilities,
        string $roomStockType,
        int $duration,
        string $partnerCode,
        bool $isSellable,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $isAvailabilityMissing = self::validateMissingAvailability($availabilities, $dateFrom, $dateTo);
        if (true === $isAvailabilityMissing) {
            $datePeriod = new \DatePeriod(
                (new \DateTime($dateFrom->format(self::DEFAULT_DATE_FORMAT))),
                new \DateInterval('P1D'),
                (new \DateTime($dateTo->format(self::DEFAULT_DATE_FORMAT)))->modify('+1 day')
            );

            foreach ($datePeriod as $date) {
                $date = $date->format(self::DEFAULT_DATE_FORMAT);
                $availabilities['stock'][] = isset($availabilities[$date]) ?
                    self::validateStockType($availabilities[$date]['stock'], $roomStockType) : '0';
            }
        } else {
            foreach ($availabilities as $availability) {
                $availabilities['stock'][] = self::validateStockType($availability['stock'], $roomStockType);
            }
        }

        return self::buildDataForGetPackage($availabilities['stock'], $duration, $partnerCode, $isSellable);
    }

    private static function validateStockType(string $stock, string $roomStockType): string
    {
        return 0 < ((int) $stock) ? $roomStockType : self::AVAILABILITY_SHORTEN_NOT_AVAILABLE;
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

    public static function convertAvailabilityTypeToExplicitQuickdataValue(
        string $type,
        int $stock,
        string $isStopSale
    ): string {
        if ('1' === $isStopSale) {
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

    public static function createDatePeriod(\DateTime $beginDate, \DateTime $endDate): \DatePeriod
    {
        if (self::DEFAULT_DATE_DIFF_VALUE === $beginDate->diff($endDate)->days) {
            throw new InvalidDatesForPeriod();
        }

        return new \DatePeriod($beginDate, new \DateInterval('P1D'), $endDate);
    }
}
