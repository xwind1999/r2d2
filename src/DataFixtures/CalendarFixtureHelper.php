<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Constants\DateTimeConstants;

class CalendarFixtureHelper
{
    private const AVAILABILITY_DATES_RANGE = '+15 days';
    private const START_DATE = 'first day of next month';

    public static function getAvailabilityCalendar(): array
    {
        $beginDate = new \DateTime(
            date(DateTimeConstants::DEFAULT_DATE_FORMAT, strtotime(self::START_DATE))
        );
        $endDate = (clone $beginDate)->modify(self::AVAILABILITY_DATES_RANGE);

        $interval = new \DateInterval(DateTimeConstants::PLUS_ONE_DAY_DATE_INTERVAL);
        $dateRange = new \DatePeriod($beginDate, $interval, $endDate);

        $calendar = [];
        foreach ($dateRange as $date) {
            $calendar[$date->format(DateTimeConstants::DEFAULT_DATE_FORMAT)] = random_int(0, 9) < 2 ? 0 : 1;
        }

        return $calendar;
    }
}
