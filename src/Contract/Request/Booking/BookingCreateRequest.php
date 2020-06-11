<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking;

use App\Contract\Request\Booking\BookingCreate\Experience;
use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
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
     * @SWG\Property(example="SBXFRJBO200101123123")
     */
    public string $bookingId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="25")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="2406")
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
     * @SWG\Property(example="EUR")
     */
    public string $currency;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="9", max="12")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="198257918")
     */
    public string $voucher;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotNull
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @SWG\Property(example="2020-01-01")
     */
    public \DateTime $startDate;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotNull
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     *
     * @SWG\Property(example="2020-01-02")
     */
    public \DateTime $endDate;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="Clean sheets please")
     */
    public ?string $customerComment = null;

    /**
     * @Assert\Type(type="array")
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @JMS\Type("array<App\Contract\Request\Booking\BookingCreate\Guest>")
     *
     * @var \App\Contract\Request\Booking\BookingCreate\Guest[]
     */
    public array $guests;

    /**
     * @Assert\Type(type="array")
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @JMS\Type("array<App\Contract\Request\Booking\BookingCreate\Room>")
     *
     * @var \App\Contract\Request\Booking\BookingCreate\Room[]
     */
    public array $rooms;
}
