<?php

declare(strict_types=1);

namespace App\Contract\Response\RoomPrice;

use App\Contract\ResponseContract;
use App\Entity\RoomPrice;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RoomPriceGetResponse extends ResponseContract
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
     * @Assert\Type(type="DateTime")
     * @Assert\Length(min="1")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime")
     */
    public \DateTime $date;

    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_integer")
     */
    public int $price;

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

    public function __construct(RoomPrice $roomPrice)
    {
        $this->uuid = $roomPrice->uuid->toString();
        $this->roomGoldenId = $roomPrice->roomGoldenId;
        $this->rateBandGoldenId = $roomPrice->rateBandGoldenId;
        $this->date = $roomPrice->date;
        $this->price = $roomPrice->price;
        $this->createdAt = $roomPrice->createdAt;
        $this->updatedAt = $roomPrice->updatedAt;
    }
}
