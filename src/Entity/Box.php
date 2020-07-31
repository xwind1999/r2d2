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
 * @ORM\Entity(repositoryClass="App\Repository\BoxRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"golden_id"})})
 */
class Box
{
    use TimestampableEntityTrait;

    public const BOX_UNIVERSE_STAY = ['STG', 'STA', 'STW', 'MTT'];

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
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    public ?string $brand = null;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    public ?string $country = null;

    /**
     * @ORM\Column(type="product_status", length=30)
     */
    public string $status;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    public ?string $currency = null;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    public ?string $universe = null;

    /**
     * @ORM\Column(type="datetime_milliseconds", nullable=true)
     */
    public ?\DateTime $externalUpdatedAt = null;

    /**
     * @var Collection<int, BoxExperience>
     *
     * @ORM\OneToMany(targetEntity="BoxExperience", mappedBy="box")
     */
    public Collection $boxExperience;

    public function __construct()
    {
        $this->boxExperience = new ArrayCollection();
    }
}
