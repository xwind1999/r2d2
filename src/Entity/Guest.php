<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GuestRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"booking_golden_id"})})
 */
class Guest
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
     * @ORM\ManyToOne(targetEntity="Booking")
     * @ORM\JoinColumn(name="booking_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Booking $booking;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $bookingGoldenId;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $externalId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public ?string $firstName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public ?string $lastName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public ?string $email = null;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public ?string $phone = null;
}
