<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener\Product;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Country
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="2", max="2")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $code;

    public static function create(string $code): Country
    {
        $country = new Country();
        $country->code = $code;

        return $country;
    }
}
