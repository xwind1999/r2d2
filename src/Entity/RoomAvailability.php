<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomAvailabilityRepository")
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"component_golden_id","date"}),
 *         @ORM\UniqueConstraint(columns={"component_uuid","date"})
 *     }
 * )
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
     * @ORM\Column(type="integer")
     */
    public int $stock;

    /**
     * @ORM\Column(type="string", length=10)
     */
    public string $type;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $isStopSale = false;

    /**
     * @ORM\Column(type="datetime_milliseconds", nullable=true)
     */
    public ?\DateTime $externalUpdatedAt = null;
}
