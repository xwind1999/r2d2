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
class Booking
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
    public string $goldenId;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $partnerGoldenId;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $experienceGoldenId;

    /**
     * @ORM\Column(type="string", length=8)
     */
    public string $type;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    public string $voucher;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    public string $brand;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    public string $country;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    public string $requestType;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public string $channel;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public string $cancellationChannel;

    /**
     * @ORM\Column(type="string", length=30)
     */
    public string $status;

    /**
     * @ORM\Column(type="integer")
     */
    public int $totalPrice;

    /**
     * @ORM\Column(type="date")
     */
    public \DateTime $startDate;

    /**
     * @ORM\Column(type="date")
     */
    public \DateTime $endDate;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $customerExternalId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public string $customerFirstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public string $customerLastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public string $customerEmail;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public string $customerPhone;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public string $customerComment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public string $partnerComment;

    /**
     * @ORM\Column(type="date")
     */
    public \DateTime $placedAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    public \DateTime $cancelledAt;
}
