<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingCreate;

use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

class Room
{
    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     *
     * @OA\Property(type="boolean", example=false)
     */
    public bool $extraRoom;

    /**
     * @Assert\Type(type="array")
     * @Assert\Valid
     * @Assert\NotBlank
     *
     * @JMS\Type("array<App\Contract\Request\Booking\BookingCreate\RoomDate>")
     *
     * @var \App\Contract\Request\Booking\BookingCreate\RoomDate[]
     */
    public array $dates;
}
