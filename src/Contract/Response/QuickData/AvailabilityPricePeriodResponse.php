<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AvailabilityPricePeriodResponse extends QuickDataResponse
{
    /**
     * @Assert\Type(type="array<App\Contract\Response\QuickData\AvailabilityPricePeriod\DayAvailabilityPrice>")
     *
     * @JMS\Type("array<App\Contract\Response\QuickData\AvailabilityPricePeriod\DayAvailabilityPrice>")
     * @JMS\SerializedName("DaysAvailabilityPrice")
     */
    public array $daysAvailabilityPrice = [];
}
