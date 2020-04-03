<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExperienceComponent
{
    use TimestampableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Room")
     * @ORM\JoinColumn(name="room_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Room $room;

    /**
     * @ORM\Column(type="string", length=45, unique=true)
     */
    public string $goldenId;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Experience")
     * @ORM\JoinColumn(name="experience_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Experience $experience;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isEnabled;

    /**
     * @ORM\Column(type="datetime")
     */
    public \DateTime $externalUpdatedAt;
}
