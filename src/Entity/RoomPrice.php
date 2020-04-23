<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomPriceRepository")
 */
class RoomPrice
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
     * @ORM\ManyToOne(targetEntity="Component")
     * @ORM\JoinColumn(name="component_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Component $component;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $componentGoldenId;

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
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    public int $price;
}
