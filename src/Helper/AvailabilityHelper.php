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
}
