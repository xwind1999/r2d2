<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RateBandRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"golden_id"})})
 */
class RateBand
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
}
