<?php

declare(strict_types=1);

namespace App\Contract\Response\BookingDate;

use App\Contract\ResponseContract;
use App\Entity\BookingDate;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BookingDateGetResponse extends ResponseContract
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
    public string $bookingGoldenId;

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
     *
     * @JMS\Type("string")
     */
    public ?string $rateBandGoldenId;

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
     * @Assert\Type(type="boolean")
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isUpsell;

    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_integer")
     */
    public int $guestsCount;

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

    public function __construct(BookingDate $bookingDate)
    {
        $this->uuid = $bookingDate->uuid->toString();
        $this->bookingGoldenId = $bookingDate->bookingGoldenId;
        $this->roomGoldenId = $bookingDate->roomGoldenId;
        $this->rateBandGoldenId = $bookingDate->rateBandGoldenId;
        $this->date = $bookingDate->date;
        $this->price = $bookingDate->price;
        $this->isUpsell = $bookingDate->isUpsell;
        $this->guestsCount = $bookingDate->guestsCount;
        $this->createdAt = $bookingDate->createdAt;
        $this->updatedAt = $bookingDate->updatedAt;
    }
}
