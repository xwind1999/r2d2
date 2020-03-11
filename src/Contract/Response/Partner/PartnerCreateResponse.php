<?php

declare(strict_types=1);

namespace App\Contract\Response\Partner;

use App\Contract\ResponseContract;
use App\Entity\Partner;
use JMS\Serializer\Annotation as JMS;
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
        $this->uuid = $partner->uuid->toString();
    }
}
