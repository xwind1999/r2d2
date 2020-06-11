<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener\PriceInformation;

use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class Price
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     *
     * @JMS\Type("float_to_integer")
     * @SWG\Property(example=10.50)
     */
    public int $amount;

    /**
     * @Assert\Currency()
     * @Assert\NotBlank()
     *
     * @JMS\Type("string")
     * @SWG\Property(example="EUR")
     */
    public string $currencyCode;
}
