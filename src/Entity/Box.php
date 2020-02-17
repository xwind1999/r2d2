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
class Box
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
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    public string $brand;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    public string $country;

    /**
     * @ORM\Column(type="string", length=30)
     */
    public string $status;
}
