<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomAvailabilityRepository")
 */
class RoomAvailability
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
     * @ORM\ManyToOne(targetEntity="Room")
     * @ORM\JoinColumn(name="room_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Room $room;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $roomGoldenId;

    /**
     * @ORM\ManyToOne(targetEntity="RateBand")
     * @ORM\JoinColumn(name="rate_band_uuid", referencedColumnName="uuid", nullable=false)
     */
    public RateBand $rateBand;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $rateBandGoldenId;

    /**
     * @ORM\Column(type="date")
     */
    public \DateTime $date;

    /**
     * @ORM\Column(type="integer")
     */
    public int $stock;

    /**
     * @ORM\Column(type="string", length=10)
     */
    public string $type;
}
