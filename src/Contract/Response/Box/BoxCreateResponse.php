<?php

declare(strict_types=1);

namespace App\Contract\Response\Box;

use App\Contract\ResponseContract;
use App\Entity\Box;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BoxCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(Box $box)
    {
        $this->uuid = $box->uuid->toString();
    }
}
