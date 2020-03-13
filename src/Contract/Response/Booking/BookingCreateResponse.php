<?php

declare(strict_types=1);

namespace App\Contract\Response\Booking;

use App\Contract\ResponseContract;
use App\Entity\Booking;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BookingCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(Booking $booking)
    {
        $this->uuid = $booking->uuid->toString();
    }
}
