<?php

declare(strict_types=1);

namespace App\Contract\Response\Partner;

use App\Contract\ResponseContract;
use App\Entity\Partner;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PartnerCreateResponse extends ResponseContract
{
    public const HTTP_CODE = 201;

    /**
     * @Assert\Uuid
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

    public function __construct(Partner $partner)
    {
        /*
         * @todo remove verification after issue is fixed on doctrine
         * @see https://github.com/doctrine/orm/issues/7999
         */
        if ($partner->uuid instanceof UuidInterface) {
            $this->uuid = $partner->uuid->toString();
        } else {
            $this->uuid = '';
        }
    }
}
