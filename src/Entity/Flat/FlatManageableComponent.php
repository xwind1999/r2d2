<?php

declare(strict_types=1);

namespace App\Entity\Flat;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(columns={"experience_golden_id"}),
 *         @ORM\Index(columns={"component_golden_id"}),
 *     }
 * )
 */
class FlatManageableComponent
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=45)
     */
    public string $boxGoldenId;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=45)
     */
    public string $experienceGoldenId;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=45)
     */
    public string $componentGoldenId;

    /**
     * @ORM\Column(type="uuid_binary_ordered_time")
     */
    public UuidInterface $componentUuid;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $partnerGoldenId;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    public int $duration;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isSellable;

    /**
     * @ORM\Column(type="room_stock_type", length=10)
     */
    public string $roomStockType;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    public ?\DateTime $lastBookableDate = null;
}
