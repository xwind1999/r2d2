<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TimestampableEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BoxExperienceRepository")
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(columns={"experience_golden_id"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"box_golden_id","experience_golden_id"})
 *     }
 * )
 */
class BoxExperience
{
    use TimestampableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Box", inversedBy="boxExperience")
     * @ORM\JoinColumn(name="box_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Box $box;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $boxGoldenId;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Experience", inversedBy="boxExperience")
     * @ORM\JoinColumn(name="experience_uuid", referencedColumnName="uuid", nullable=false)
     */
    public Experience $experience;

    /**
     * @ORM\Column(type="string", length=45)
     */
    public string $experienceGoldenId;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $isEnabled;

    /**
     * @ORM\Column(type="datetime_milliseconds", nullable=true)
     */
    public ?\DateTime $externalUpdatedAt = null;
}
