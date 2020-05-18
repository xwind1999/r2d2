<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"golden_id"})})
 */
class Booking
{
    use TimestampableEntityTrait;

    public const BOOKING_STATUS_CREATED = 'created';
    public const BOOKING_STATUS_COMPLETE = 'complete';

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid_binary_ordered_time", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     *
     * @CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
     */
    public UuidInterface $uuid;

    /**
     * @ORM\Column(type="string", length=45, unique=true)
     */
    public string $goldenId;

    /**
     * @ORM\ManyToOne(targetEntity="Partner")
     * @ORM\JoinColumn(name="partner_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Partner $partner;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $partnerGoldenId;

    /**
     * @ORM\ManyToOne(targetEntity="Experience")
     * @ORM\JoinColumn(name="experience_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Experience $experience;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $experienceGoldenId;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    public ?string $voucher = null;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    public ?string $brand = null;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    public ?string $country = null;

    /**
     * @ORM\Column(type="string", length=30)
     */
    public string $status;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
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
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $customerComment = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    public ?\DateTime $cancelledAt = null;

    /**
     * @var Collection<int, Guest>
     *
     * @ORM\OneToMany(targetEntity="Guest", mappedBy="booking", cascade={"persist", "remove"})
     */
    public Collection $guest;

    /**
     * @var Collection<int, BookingDate>
     *
     * @ORM\OneToMany(targetEntity="BookingDate", mappedBy="booking", cascade={"persist", "remove"})
     */
    public Collection $dates;

    /**
     * @ORM\Column(type="json")
     */
    public array $components;

    public function __construct()
    {
        $this->guest = new ArrayCollection();
        $this->dates = new ArrayCollection();
        $this->components = [];
    }
}
