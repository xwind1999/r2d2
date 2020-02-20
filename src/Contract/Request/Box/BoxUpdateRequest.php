<?php

declare(strict_types=1);

namespace App\Contract\Request\Box;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BoxUpdateRequest extends BoxCreateRequest implements RequestBodyInterface, ValidatableRequest
{
    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;
}
