<?php

declare(strict_types=1);

namespace App\Helper;

use App\Entity\Component;

class DateDurationExpander
{
    /**
     * @param array<string, \DateTime> $changedDates
     *
     * @return array<string, \DateTime>
     */
    public function expandDatesForComponentDuration(Component $component, array $changedDates): array
    {
        if ($component->duration > 1) {
            $extraDates = [];
            foreach ($changedDates as $date) {
                $beginDate = (clone $date)->modify(sprintf('-%s day', $component->duration - 1));
                $endDate = (clone $date)->modify(sprintf('+%s day', $component->duration));
                $interval = new \DateInterval('P1D');
                $period = new \DatePeriod($beginDate, $interval, $endDate);
                foreach ($period as $extraDate) {
                    /** @var string $formattedDate */
                    $formattedDate = $extraDate->format('Y-m-d');
                    $extraDates[$formattedDate] = $extraDate;
                }
            }
            $changedDates += $extraDates;
        }

        return $changedDates;
    }
}
