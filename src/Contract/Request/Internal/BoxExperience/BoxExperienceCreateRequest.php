<?php

declare(strict_types=1);

namespace App\Contract\Request\Internal\BoxExperience;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BoxExperienceCreateRequest implements RequestBodyInterface, ValidatableRequest
{
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
     * @Assert\NotNull
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
}
