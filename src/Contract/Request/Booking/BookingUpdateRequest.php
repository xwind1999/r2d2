<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

class BookingUpdateRequest implements RequestBodyInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="20")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("bookingId")
     *
     * @SWG\Property(example="SBXFRJBO200101123123")
     */
    public string $bookingId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="9", max="12")
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="198257918")
     */
    public ?string $voucher = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice({"complete", "cancelled"})
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @SWG\Property(example="complete")
     */
    public string $status;
}
