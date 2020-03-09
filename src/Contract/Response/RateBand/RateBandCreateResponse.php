<?php

declare(strict_types=1);

namespace App\Contract\Response\RateBand;

use App\Contract\ResponseContract;
use App\Entity\RateBand;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
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
        /*
         * @todo remove verification after issue is fixed on doctrine
         * @see https://github.com/doctrine/orm/issues/7999
         */
        if ($rateBand->uuid instanceof UuidInterface) {
            $this->uuid = $rateBand->uuid->toString();
        } else {
            $this->uuid = '';
        }
    }
}
