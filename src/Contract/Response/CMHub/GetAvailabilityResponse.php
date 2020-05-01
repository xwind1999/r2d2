<?php

declare(strict_types=1);

namespace App\Contract\Response\CMHub;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class GetAvailabilityResponse extends CMHubResponse
{
    /**
     * @Assert\Type(type="DateTime")
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $date;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero()
     *
     * @JMS\Type("strict_integer")
     */
    public int $quantity;
}
