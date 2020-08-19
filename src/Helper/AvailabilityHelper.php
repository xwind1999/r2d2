<?php

declare(strict_types=1);

namespace App\Helper;

class AvailabilityHelper
{
    public const AVAILABILITY_TYPE_ON_REQUEST = 'on_request';
    public const AVAILABILITY_SHORTEN_INSTANT = '1';
    public const AVAILABILITY_SHORTEN_ON_REQUEST = 'r';
    public const AVAILABILITY_SHORTEN_NOT_AVAILABLE = '0';

    private const DEFAULT_DATE_TIME_FORMAT = 'Y-m-d';
    private const DEFAULT_DATE_DIFF_VALUE = 0;

    public static function convertToShortType(array $availabilities): array
    {
        $shortenArray = [];

        foreach ($availabilities as $availability) {
            if (0 === $availability['stock']) {
                $shortenArray[] = self::AVAILABILITY_SHORTEN_NOT_AVAILABLE;
            } elseif (self::AVAILABILITY_TYPE_ON_REQUEST === $availability['type']) {
                $shortenArray[] = self::AVAILABILITY_SHORTEN_ON_REQUEST;
            } else {
                $shortenArray[] = self::AVAILABILITY_SHORTEN_INSTANT;
            }
        }

        return $shortenArray;
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

        $dateFlag = $dateFrom->setTime(0, 0, 0);
        $availabilityPosition = 0;

        for ($i = 0; $i < $numberOfNights; ++$i) {
            if (
                empty($availabilities[$availabilityPosition]['date']) ||
                0 !== $dateFlag->diff($availabilities[$availabilityPosition]['date'])->days
            ) {
                $returnAvailabilities[] = [
                    'stock' => 0,
                    'date' => clone $dateFlag,
                    'type' => 'stock',
                    'componentGoldenId' => $componentId,
                ];
            } else {
                $returnAvailabilities[] = $availabilities[$availabilityPosition];
                ++$availabilityPosition;
            }
            $dateFlag->modify('+1 day');
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

    public static function convertAvailableValueToRequest(string $availability): string
    {
        return ('Available' === $availability) ? 'Request' : $availability;
    }

    public static function mapRoomAvailabilitiesToExperience(
        array $components,
        array $roomAvailabilities,
        int $numberOfNights
    ): array {
        $returnArray = [];

        foreach ($components as $component) {
            $duration = $component[0]['duration'] ?: 0;
            if (!empty($roomAvailabilities[$component[0]['goldenId']]) && $duration <= $numberOfNights) {
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
