<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceComponentRepository")
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(columns={"component_golden_id"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"experience_golden_id","component_golden_id"})
 *     }
 * )
 */
class ExperienceComponent
{
    use TimestampableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Experience", inversedBy="experienceComponent")
     * @ORM\JoinColumn(name="experience_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Experience $experience;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public string $experienceGoldenId;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Component", inversedBy="experienceComponent")
     * @ORM\JoinColumn(name="component_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Component $component;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    public string $componentGoldenId;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isEnabled;

    /**
     * @ORM\Column(type="datetime_milliseconds", nullable=true)
     */
    public ?\DateTime $externalUpdatedAt = null;
}
