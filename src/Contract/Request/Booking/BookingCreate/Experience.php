<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingCreate;

use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class Experience
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="3216334")
     */
    public string $id;

    /**
     * @Assert\Type(type="array")
     * @Assert\All({
     *     @Assert\Type(type="string"),
     *     @Assert\NotNull,
     *     @Assert\Length(min=1, max=255)
     * })
     *
     * @JMS\Type("array<string>")
     *
     * @SWG\Property(example={"Cup of tea", "Una noche muy buena"})
     */
    public array $components;
}
