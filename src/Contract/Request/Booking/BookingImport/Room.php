<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingImport;

use App\Contract\Request\Booking\BookingCreate\Room as RoomCreateRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Room extends RoomCreateRequest
{
    /**
     * @Assert\Type(type="boolean")
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $extraRoom = false;

    /**
     * @Assert\Type(type="array")
     *
     * @JMS\Type("array<App\Contract\Request\Booking\BookingImport\RoomDate>")
     *
     * @var RoomDate[]
     */
    public array $dates = [];
}
