<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener\Product;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Partner
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $id;

    public static function create(string $id): Partner
    {
        $partner = new Partner();
        $partner->id = $id;

        return $partner;
    }
}
