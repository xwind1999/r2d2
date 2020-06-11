<?php

declare(strict_types=1);

namespace App\Contract\Request\CMHub;

use App\Helper\Request\RequestQueryInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class GetAvailabilityRequest implements RequestQueryInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero()
     * @Assert\NotBlank
     *
     * @JMS\Type("integer")
     */
    public int $productId;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTimeInterface $start;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTimeInterface $end;
}
