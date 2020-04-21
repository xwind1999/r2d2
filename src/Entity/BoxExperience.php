<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BoxExperienceRepository")
 */
class BoxExperience
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Box")
     * @ORM\JoinColumn(name="box_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Box $box;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public string $boxGoldenId;

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
