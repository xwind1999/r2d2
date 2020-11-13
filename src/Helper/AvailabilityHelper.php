<?php

declare(strict_types=1);

namespace App\Helper;

use App\Constants\AvailabilityConstants;
use App\Constants\DateTimeConstants;
use App\Constraint\RoomStockTypeConstraint;

class AvailabilityHelper
{
    private const DEFAULT_DATE_DIFF_VALUE = 0;

    public function convertToShortType(array $stocksList, string $roomStockType): array
    {
        $availabilityValues = [];
        foreach ($stocksList as $stock) {
            $availabilityValues[] = (0 < (int) $stock) ? $roomStockType : AvailabilityConstants::AVAILABILITY_SHORTEN_NOT_AVAILABLE;
        }

        return $availabilityValues;
    }

    public function getRoomStockShortType(string $roomStockType): string
    {
        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT === $roomStockType ||
            RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK === $roomStockType) {
            return AvailabilityConstants::AVAILABILITY_SHORTEN_INSTANT;
        }

        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST === $roomStockType) {
            return AvailabilityConstants::AVAILABILITY_SHORTEN_ON_REQUEST;
        }

        return AvailabilityConstants::AVAILABILITY_SHORTEN_NOT_AVAILABLE;
    }

    public function fillMissingAvailabilitiesForAvailabilityPrice(
        array $availabilities,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $isAvailabilityMissing = $this->validateMissingAvailability($availabilities, $dateFrom, $dateTo);
        if (false === $isAvailabilityMissing) {
            return $availabilities;
        }
        $returnAvailabilities = [];
        $datePeriod = new \DatePeriod(
            (new \DateTime($dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT))),
            new \DateInterval(DateTimeConstants::PLUS_ONE_DAY_DATE_INTERVAL),
            (new \DateTime($dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)))->modify('+1 day')
        );
        foreach ($datePeriod as $date) {
            $date = $date->format(DateTimeConstants::DEFAULT_DATE_FORMAT).DateTimeConstants:: DEFAULT_TIME_FORMAT;
            if (!isset($availabilities[$date])) {
                $returnAvailabilities[$date] = [
                    'Date' => $date,
                    'AvailabilityValue' => 0,
                    'AvailabilityStatus' => AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE,
                    'SellingPrice' => 0.00,
                    'BuyingPrice' => 0.00,
                ];
            } else {
                $returnAvailabilities[$date] = $availabilities[$date];
            }
        }

        return $returnAvailabilities;
    }

    public function buildDataForGetPackage(
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

    public function fillMissingAvailabilityForGetPackage(
        array $availabilities,
        string $roomStockType,
        int $duration,
        string $partnerCode,
        bool $isSellable,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $isAvailabilityMissing = $this->validateMissingAvailability($availabilities, $dateFrom, $dateTo);
        if (true === $isAvailabilityMissing) {
            $datePeriod = new \DatePeriod(
                (new \DateTime($dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT))),
                new \DateInterval(DateTimeConstants::PLUS_ONE_DAY_DATE_INTERVAL),
                (new \DateTime($dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT)))->modify('+1 day')
            );

            foreach ($datePeriod as $date) {
                $date = $date->format(DateTimeConstants::DEFAULT_DATE_FORMAT);
                $availabilities['stock'][] = isset($availabilities[$date]) ?
                    $this->validateStockType($availabilities[$date]['stock'], $roomStockType)
                    : (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST === $roomStockType ? AvailabilityConstants::AVAILABILITY_SHORTEN_ON_REQUEST : '0');
            }
        } else {
            foreach ($availabilities as $availability) {
                $availabilities['stock'][] = $this->validateStockType($availability['stock'], $roomStockType);
            }
        }

        return $this->buildDataForGetPackage($availabilities['stock'], $duration, $partnerCode, $isSellable);
    }

    public function buildDataForGetRange(array $availabilities): array
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

    public function convertAvailabilityTypeToExplicitQuickdataValue(
        string $type,
        int $stock,
        string $isStopSale
    ): string {
        if ('1' === $isStopSale) {
            return AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE;
        }

        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK === $type && $stock > 0) {
            return AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_AVAILABLE;
        }

        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST === $type) {
            return AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_REQUEST;
        }

        return AvailabilityConstants::AVAILABILITY_PRICE_PERIOD_UNAVAILABLE;
    }

    public function getRealStock(
        array $roomAvailabilities,
        array $bookingDateStock
    ): array {
        foreach ($bookingDateStock as $booking) {
            foreach ($roomAvailabilities as $key => $availability) {
                if (isset($availability['date'], $availability['stock']) &&
                    $availability['date'] === $booking['date'] && (
                        (
                            isset($availability['componentGoldenId']) &&
                            $availability['componentGoldenId'] === $booking['componentGoldenId']
                        )
                        ||
                        (
                            isset($availability['experienceGoldenId']) &&
                            $availability['experienceGoldenId'] === $booking['experienceGoldenId']
                        )
                    )
                ) {
                    $roomAvailabilities[$key]['stock'] = (string) max(
                        $roomAvailabilities[$key]['stock'] - $booking['usedStock'],
                        AvailabilityConstants::AVAILABILITY_SHORTEN_NOT_AVAILABLE
                    );
                }
            }
        }

        return $roomAvailabilities;
    }

    private function validateMissingAvailability(
        array $availabilities,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): bool {
        $dateFrom = new \DateTime($dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $dateTo = new \DateTime($dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $numberOfNights = ($dateTo->diff($dateFrom)->days ?: self::DEFAULT_DATE_DIFF_VALUE) + 1;

        return $numberOfNights !== count($availabilities);
    }

    private function validateStockType(string $stock, string $roomStockType): string
    {
        return 0 < ((int) $stock) ? $roomStockType : AvailabilityConstants::AVAILABILITY_SHORTEN_NOT_AVAILABLE;
    }
}
