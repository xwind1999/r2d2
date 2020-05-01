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
 * @ORM\Entity(repositoryClass="App\Repository\ComponentRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"golden_id"})})
 */
class Component
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
     * @ORM\Column(type="integer")
     */
    public int $inventory;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    public ?int $duration = null;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isSellable;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isReservable;

    /**
     * @ORM\Column(type="string", length=8)
     */
    public string $status;

    /**
     * @var Collection<int, ExperienceComponent>
     *
     * @ORM\OneToMany(targetEntity="ExperienceComponent", mappedBy="component", fetch="EXTRA_LAZY")
     */
    public Collection $experienceComponent;

    public function __construct()
    {
        $this->experienceComponent = new ArrayCollection();
    }
}
