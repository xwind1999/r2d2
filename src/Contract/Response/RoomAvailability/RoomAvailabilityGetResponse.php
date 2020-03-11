<?php

declare(strict_types=1);

namespace App\Contract\Response\RoomAvailability;

use App\Contract\ResponseContract;
use App\Entity\RoomAvailability;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RoomAvailabilityGetResponse extends ResponseContract
{
    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $roomGoldenId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $rateBandGoldenId;

    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_integer")
     */
    public int $stock;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $date;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="10")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $type;

    /**
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime")
     */
    public \DateTime $createdAt;

    /**
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime")
     */
    public \DateTime $updatedAt;

    public function __construct(RoomAvailability $roomAvailability)
    {
        $this->uuid = $roomAvailability->uuid->toString();
        $this->roomGoldenId = $roomAvailability->roomGoldenId;
        $this->rateBandGoldenId = $roomAvailability->rateBandGoldenId;
        $this->stock = $roomAvailability->stock;
        $this->date = $roomAvailability->date;
        $this->type = $roomAvailability->type;
        $this->createdAt = $roomAvailability->createdAt;
        $this->updatedAt = $roomAvailability->updatedAt;
    }
}
