<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener\Product;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Universe
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $id;

    public static function create(string $id): Universe
    {
        $universe = new Universe();
        $universe->id = $id;

        return $universe;
    }
}
