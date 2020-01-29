<?php

declare(strict_types=1);

namespace App\Contract\Request\Test;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class PostEchoRequest implements RequestBodyInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $message;
}
