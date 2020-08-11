<?php

declare(strict_types=1);

namespace App\DataFixtures;

class CalendarFixtureHelper
{
    private const AVAILABILITY_DATES_RANGE = '+14 days';

    public static function getAvailabilityCalendar(int $days = 0): array
    {
        if (0 === $days) {
            $days = static::AVAILABILITY_DATES_RANGE;
        }

        $beginDate = new \DateTime(date('Y-m-d', strtotime('first day of next month')));
        $endDate = (clone $beginDate)->modify(self::AVAILABILITY_DATES_RANGE);
        $endDate = $endDate->modify('+1 day');

        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($beginDate, $interval, $endDate);

        $calendar = [];
        foreach ($dateRange as $date) {
            $calendar[$date->format('Y-m-d')] = random_int(0, 9) < 2 ? 0 : 1;
        }

        return $calendar;
    }
}
