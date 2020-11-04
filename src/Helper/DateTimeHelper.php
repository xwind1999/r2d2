<?php

declare(strict_types=1);

namespace App\Helper;

use App\Constants\DateTimeConstants;
use App\Exception\Helper\InvalidDatesForPeriod;

class DateTimeHelper
{
    private const DEFAULT_DATE_DIFF_VALUE = 0;

    public static function createDatePeriod(\DateTime $beginDate, \DateTime $endDate): \DatePeriod
    {
        if (self::DEFAULT_DATE_DIFF_VALUE === $beginDate->diff($endDate)->days) {
            throw new InvalidDatesForPeriod();
        }

        return new \DatePeriod($beginDate, new \DateInterval(DateTimeConstants::PLUS_ONE_DAY_DATE_INTERVAL), $endDate);
    }
}
