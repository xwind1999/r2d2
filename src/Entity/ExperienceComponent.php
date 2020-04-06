<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceComponentRepository")
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
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public string $roomGoldenId;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Experience")
     * @ORM\JoinColumn(name="experience_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Experience $experience;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public string $experienceGoldenId;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isEnabled;

    /**
     * @ORM\Column(type="datetime")
     */
    public \DateTime $externalUpdatedAt;
}
