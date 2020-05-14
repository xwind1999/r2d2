<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingCreate;

use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class RoomDate
{
    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotNull
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @SWG\Property(example="2020-01-01")
     */
    public \DateTime $day;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     * @Assert\NotNull
     *
     * @JMS\Type("strict_integer")
     *
     * @SWG\Property(type="integer", example=5500)
     */
    public int $price;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     * @JMS\SerializedName("extraNight")
     *
     * @SWG\Property(type="boolean", example=false)
     */
    public bool $extraNight;
}
