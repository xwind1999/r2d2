<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 */
class BookingDate
{
    use TimestampableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid_binary_ordered_time", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     *
     * @CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
     */
    public UuidInterface $uuid;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $bookingGoldenId;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $roomGoldenId;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public ?string $rateBandGoldenId;

    /**
     * @ORM\Column(type="date")
     */
    public \DateTime $date;

    /**
     * @ORM\Column(type="integer")
     */
    public int $price;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isUpsell;

    /**
     * @ORM\Column(type="integer")
     */
    public int $guestsCount;
}
