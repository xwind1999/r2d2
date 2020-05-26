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
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"golden_id"})})
 */
class Experience
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
     * @ORM\Column(type="string", length=255)
     */
    public string $name;

    /**
     * @ORM\Column(type="text")
     */
    public string $description;

    /**
     * @var Collection<int, BoxExperience>
     *
     * @ORM\OneToMany(targetEntity="BoxExperience", mappedBy="experience", fetch="EXTRA_LAZY")
     */
    public Collection $boxExperience;

    /**
     * @var Collection<int, ExperienceComponent>
     *
     * @ORM\OneToMany(targetEntity="ExperienceComponent", mappedBy="experience", fetch="EXTRA_LAZY")
     */
    public Collection $experienceComponent;

    /**
     * @ORM\Column(type="integer", length=2, nullable=true, options={"fixed": true})
     */
    public ?int $peopleNumber;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    public ?int $duration;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public ?int $price;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    public ?string $commissionType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public ?int $commission;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public ?\DateTime $priceUpdatedAt = null;

    public function __construct()
    {
        $this->boxExperience = new ArrayCollection();
        $this->experienceComponent = new ArrayCollection();
    }
}
