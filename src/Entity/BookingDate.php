<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingDateRepository")
 */
class BookingDate
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid_binary_ordered_time", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     *
     * @CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
     */
    public UuidInterface $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Booking", inversedBy="bookingDate", cascade={"persist"})
     * @ORM\JoinColumn(name="booking_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Booking $booking;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $bookingGoldenId;

    /**
     * @ORM\ManyToOne(targetEntity="Component")
     * @ORM\JoinColumn(name="component_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Component $component;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $componentGoldenId;

    /**
     * @ORM\Column(type="date")
     */
    public \DateTime $date;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    public int $price;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    public int $guestsCount;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isExtraNight;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isExtraRoom;
}
