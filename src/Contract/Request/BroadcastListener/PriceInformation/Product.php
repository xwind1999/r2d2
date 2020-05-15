<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener\PriceInformation;

use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class Product
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     * @SWG\Property(example="481d7e979637c39f6864d709")
     */
    public string $id;
}
