<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingImport;

use App\Contract\Request\Booking\BookingCreate\Guest as BookingCreateGuest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Guest extends BookingCreateGuest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     * @JMS\SerializedName ("firstname")
     */
    public string $firstName;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     * @JMS\SerializedName ("lastname")
     */
    public string $lastName;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     * @Assert\Email(mode="html5")
     *
     * @JMS\Type("string")
     */
    public ?string $email = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(max="255")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("telephone")
     */
    public ?string $phone = null;
}
