<?php

declare(strict_types=1);

namespace App\Contract\Response\Room;

use App\Contract\ResponseContract;
use App\Entity\Room;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RoomCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(Room $experience)
    {
        $this->uuid = $experience->uuid->toString();
    }
}
