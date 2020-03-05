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
class Partner
{
    use TimestampableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid_binary_ordered_time", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     *
     * @CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
     *
     * @todo remove nullable after issue is fixed on doctrine
     *
     * @see https://github.com/doctrine/orm/issues/7999
     */
    public ?UuidInterface $uuid;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $goldenId;

    /**
     * @ORM\Column(type="string", length=8)
     */
    public string $status;

    /**
     * @ORM\Column(type="string", length=3)
     */
    public string $currency;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    public ?\DateTime $ceaseDate;
}
