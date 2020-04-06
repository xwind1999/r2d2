<?php

declare(strict_types=1);

namespace App\Contract\Response\ExperienceComponent;

use App\Contract\ResponseContract;
use App\Entity\ExperienceComponent;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractExperienceComponentResponse extends ResponseContract
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $roomGoldenId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $experienceGoldenId;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isEnabled;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime")
     */
    public \DateTime $externalUpdatedAt;

    public function __construct(ExperienceComponent $experienceComponent)
    {
        $this->roomGoldenId = $experienceComponent->roomGoldenId;
        $this->experienceGoldenId = $experienceComponent->experienceGoldenId;
        $this->isEnabled = $experienceComponent->isEnabled;
        $this->externalUpdatedAt = $experienceComponent->externalUpdatedAt;
    }
}
