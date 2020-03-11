<?php

declare(strict_types=1);

namespace App\Contract\Response\BookingDate;

use App\Contract\ResponseContract;
use App\Entity\BookingDate;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BookingDateCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(BookingDate $bookingDate)
    {
        $this->uuid = $bookingDate->uuid->toString();
    }
}
