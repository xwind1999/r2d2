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

    public static function calculateAvailabilitiesByDuration(int $duration, array $roomAvailabilities): array
    {
        $returnArray = [];
        $numberOfAvailabilities = 0;
        $lastKey = array_key_last($roomAvailabilities);

        // Set default of return array as Reserve and set the number of Availabilities
        // Return array will be like [numberOfAvailabilities,'r','r,...]
        foreach ($roomAvailabilities as $index => $availability) {
            $index = (int) $index;
            $returnArray[] = 'r';
            // In case there is available and not the end of array, increase the numberOfAvailabilities
            if (0 < $availability['stock'] && $lastKey !== $index) {
                ++$numberOfAvailabilities;
                continue;
            }

            // In case there is available and the end of array, increase the numberOfAvailabilities and index
            if (0 < $availability['stock'] && $lastKey === $index) {
                ++$numberOfAvailabilities;
                ++$index;
            }

            // Set the legit $numberOfAvailabilities of the start index of Availabilities if suitable with the Component duration
            $returnArray[$index - $numberOfAvailabilities] = ($numberOfAvailabilities - $duration) >= 0 ?
                ($numberOfAvailabilities - $duration + 1) : 'r';
            $numberOfAvailabilities = 0;
        }

        $numberOfAvailabilities = 0;
        // Run through the array to fill all the availabilities
        foreach ($returnArray as &$item) {
            if ('r' !== $item) {
                $numberOfAvailabilities = $item;
                $item = '1';
            } elseif (0 < $numberOfAvailabilities - 1) {
                $item = '1';
                --$numberOfAvailabilities;
            } else {
                $numberOfAvailabilities = 0;
            }
        }

        return $returnArray;
    }
}
