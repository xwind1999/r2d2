<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking;

use App\Contract\Request\Booking\BookingCreate\Experience;
use App\Contract\Request\Booking\BookingCreate\Guest;
use App\Contract\Request\Booking\BookingCreate\Room;
use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

class BookingCreateRequest implements RequestBodyInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="20")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @OA\Property(example="SBXFRJBO200101123123")
     */
    public string $bookingId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="25")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @OA\Property(example="2406")
     */
    public string $box;

    /**
     * @Assert\Type(type="App\Contract\Request\Booking\BookingCreate\Experience")
     * @Assert\Valid
     * @Assert\NotNull
     *
     * @JMS\Type("App\Contract\Request\Booking\BookingCreate\Experience")
     */
    public Experience $experience;

    /**
     * @Assert\Currency()
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @OA\Property(example="EUR")
     */
    public string $currency;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="9", max="12")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @OA\Property(example="198257918")
     */
    public string $voucher;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotNull
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @OA\Property(example="2020-01-01")
     */
    public \DateTime $startDate;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotNull
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @OA\Property(example="2020-01-02")
     */
    public \DateTime $endDate;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     *
     * @OA\Property(example="Clean sheets please")
     */
    public ?string $customerComment = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(choices=\App\Constraint\AvailabilityTypeConstraint::VALID_VALUES)
     *
     * @JMS\Type("string")
     *
     * @OA\Property(example="instant")
     */
    public ?string $availabilityType = null;

    /**
     * @Assert\Type(type="array")
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @JMS\Type("array<App\Contract\Request\Booking\BookingCreate\Guest>")
     *
     * @var Guest[]
     */
    public array $guests;

    /**
     * @Assert\Type(type="array")
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @JMS\Type("array<App\Contract\Request\Booking\BookingCreate\Room>")
     *
     * @var Room[]
     */
    public array $rooms;
}
