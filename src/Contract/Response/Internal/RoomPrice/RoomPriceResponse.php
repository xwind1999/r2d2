<?php

declare(strict_types=1);

namespace App\Contract\Response\Internal\RoomPrice;

use App\Contract\ResponseContract;
use App\Entity\RoomPrice;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

abstract class RoomPriceResponse extends ResponseContract
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
    public string $componentGoldenId;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\Length(min="1")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $date;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
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
        $this->componentGoldenId = $roomPrice->componentGoldenId;
        $this->date = $roomPrice->date;
        $this->price = $roomPrice->price;
        $this->createdAt = $roomPrice->createdAt;
        $this->updatedAt = $roomPrice->updatedAt;
    }
}
