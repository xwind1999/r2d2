<?php

declare(strict_types=1);

namespace App\Contract\Response\Internal\BoxExperience;

use App\Contract\ResponseContract;
use App\Entity\BoxExperience;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BoxExperienceCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $boxGoldenId;

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
     *
     * @JMS\Type("DateTime")
     */
    public ?\DateTime $externalUpdatedAt = null;

    public function __construct(BoxExperience $boxExperience)
    {
        $this->boxGoldenId = $boxExperience->boxGoldenId;
        $this->experienceGoldenId = $boxExperience->experienceGoldenId;
        $this->externalUpdatedAt = $boxExperience->externalUpdatedAt;
        $this->isEnabled = $boxExperience->isEnabled;
    }
}
