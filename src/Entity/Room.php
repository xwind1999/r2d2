<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 */
class Room
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
     * @ORM\Column(type="boolean")
     */
    public bool $isSellable;

    /**
     * @ORM\Column(type="string", length=8)
     */
    public string $status;
}
