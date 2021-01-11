<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

class BookingUpdateRequest implements RequestBodyInterface, ValidatableRequest
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
     * @Assert\Length(min="9", max="12")
     *
     * @JMS\Type("string")
     *
     * @OA\Property(example="198257918")
     */
    public ?string $voucher = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(callback={"\App\Constraint\BookingStatusConstraint","getValidValuesForUpdate"})
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     *
     * @OA\Property(example="complete")
     */
    public string $status;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(callback={"\App\Constraint\BookingChannelConstraint","getValidValues"})
     * @JMS\Type("string")
     *
     * @OA\Property(example="jarvis-booking")
     */
    public ?string $lastStatusChannel = null;
}
