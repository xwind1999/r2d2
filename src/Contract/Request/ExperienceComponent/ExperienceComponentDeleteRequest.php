<?php

declare(strict_types=1);

namespace App\Contract\Request\ExperienceComponent;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ExperienceComponentDeleteRequest implements RequestBodyInterface, ValidatableRequest
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
}
