<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingImport;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RoomDate
{
    /**
     * @Assert\Type(type="DateTime")
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $day;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("strict_integer")
     */
    public int $price;

    /**
     * @Assert\Type(type="boolean")
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $extraNight;
}
