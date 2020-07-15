<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PartnerRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"golden_id"})})
 */
class Partner
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
     * @ORM\Column(type="partner_status", length=16)
     */
    public string $status;

    /**
     * @ORM\Column(type="string", length=3)
     */
    public string $currency;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isChannelManagerActive;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    public ?\DateTime $ceaseDate = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public ?\DateTime $externalUpdatedAt = null;
}
