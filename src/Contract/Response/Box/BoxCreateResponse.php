<?php

declare(strict_types=1);

namespace App\Contract\Response\Box;

use App\Contract\ResponseContract;
use App\Entity\Box;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
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
        /*
         * @todo remove verification after issue is fixed on doctrine
         * @see https://github.com/doctrine/orm/issues/7999
         */
        if ($box->uuid instanceof UuidInterface) {
            $this->uuid = $box->uuid->toString();
        } else {
            $this->uuid = '';
        }
    }
}
