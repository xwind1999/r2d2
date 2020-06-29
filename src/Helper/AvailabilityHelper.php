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

    public static function mapRoomAvailabilitiesToExperience(array $components, array $roomAvailabilities, int $numberOfDays): array
    {
        $returnArray = [];

        foreach ($roomAvailabilities as $availability) {
            if (!empty($components[$availability['componentGoldenId']])) {
                $component = $components[$availability['componentGoldenId']];
                $returnArray[] = [
                    'Package' => $component['experienceGoldenId'],
                    'Request' => 0,
                    'Stock' => $numberOfDays,
                ];
            }
        }

        return $returnArray;
    }
}
