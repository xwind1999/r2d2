<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingCreate;

use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class Guest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="Hermano")
     */
    public string $firstName;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="Guido")
     */
    public string $lastName;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     * @Assert\Email(mode="html5")
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="maradona@worldcup.ar")
     */
    public ?string $email = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="123 123 123")
     */
    public ?string $phone = null;
}
