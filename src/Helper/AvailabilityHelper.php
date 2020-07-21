<?php

declare(strict_types=1);

namespace App\Helper;

class AvailabilityHelper
{
    public static function convertToRequestType(array $availabilities): array
    {
        foreach ($availabilities as &$availability) {
            if ('1' == $availability) {
                $availability = 'r';
            }
        }

        return $availabilities;
    }

    public static function convertAvailableValueToRequest(string $availability): string
    {
        return ('Available' == $availability) ? 'Request' : $availability;
    }

    public static function mapRoomAvailabilitiesToExperience(array $components, array $roomAvailabilities, int $numberOfNights): array
    {
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
