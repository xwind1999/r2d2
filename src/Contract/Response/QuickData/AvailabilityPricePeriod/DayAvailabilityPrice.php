<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData\AvailabilityPricePeriod;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class DayAvailabilityPrice
{
    /**
     * @Assert\Type(type="datetime")
     *
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.u', '', 'Y-m-d\TH:i:s.uuT'>")
     * @JMS\SerializedName("Date")
     */
    public \DateTime $date;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("AvailabilityValue")
     */
    public int $availabilityValue;

    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("AvailabilityStatus")
     */
    public string $availabilityStatus;

    /**
     * @Assert\Type(type="float")
     *
     * @JMS\Type("float")
     * @JMS\SerializedName("SellingPrice")
     */
    public float $sellingPrice;

    /**
     * @Assert\Type(type="float")
     *
     * @JMS\Type("float")
     * @JMS\SerializedName("BuyingPrice")
     */
    public float $buyingPrice;
}
