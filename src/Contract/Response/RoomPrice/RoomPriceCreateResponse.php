<?php

declare(strict_types=1);

namespace App\Contract\Response\RoomPrice;

use App\Contract\ResponseContract;
use App\Entity\RoomPrice;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RoomPriceCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(RoomPrice $roomPrice)
    {
        $this->uuid = $roomPrice->uuid->toString();
    }
}
