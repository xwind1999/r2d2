<?php

declare(strict_types=1);

namespace App\Contract\Response\Experience;

use App\Contract\ResponseContract;
use App\Entity\Experience;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ExperienceCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(Experience $experience)
    {
        $this->uuid = $experience->uuid->toString();
    }
}
