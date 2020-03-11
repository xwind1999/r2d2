<?php

declare(strict_types=1);

namespace App\Contract\Response\RoomAvailability;

use App\Contract\ResponseContract;
use App\Entity\RoomAvailability;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RoomAvailabilityCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(RoomAvailability $roomAvailability)
    {
        $this->uuid = $roomAvailability->uuid->toString();
    }
}
