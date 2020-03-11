<?php

declare(strict_types=1);

namespace App\Contract\Response\RateBand;

use App\Contract\ResponseContract;
use App\Entity\RateBand;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RateBandCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(RateBand $rateBand)
    {
        $this->uuid = $rateBand->uuid->toString();
    }
}
