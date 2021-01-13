<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking\BookingImport;

use App\Contract\Request\Booking\BookingCreate\Experience;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BookingImportRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="25")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     */
    public string $bookingId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="25")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
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
     */
    public string $currency;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="9", max="12")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     */
    public string $voucher;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotNull
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $startDate;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotNull
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $endDate;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     */
    public ?string $customerComment = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(choices=\App\Constraint\AvailabilityTypeConstraint::VALID_VALUES)
     *
     * @JMS\Type("string")
     */
    public ?string $availabilityType = null;

    /**
     * @Assert\Type(type="array")
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @JMS\Type("array<App\Contract\Request\Booking\BookingImport\Guest>")
     *
     * @var Guest[]
     */
    public array $guests;

    /**
     * @Assert\Type(type="array")
     * @Assert\Valid
     *
     * @JMS\Type("array<App\Contract\Request\Booking\BookingImport\Room>")
     *
     * @var Room[]
     */
    public array $rooms = [];

    public function getContext(): array
    {
        return [
            'booking_golden_id' => $this->bookingId,
            'box_id' => $this->box,
            'experience_id' => $this->experience->id,
            'currency' => $this->currency,
            'voucher' => $this->voucher,
            'booking_start_date' => $this->startDate->format('Y-m-d'),
            'booking_end_date' => $this->endDate->format('Y-m-d'),
        ];
    }
}
