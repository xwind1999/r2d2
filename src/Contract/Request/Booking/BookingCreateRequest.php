<?php

declare(strict_types=1);

namespace App\Contract\Request\Booking;

use App\Contract\Request\Guest\GuestRequest;
use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BookingCreateRequest implements RequestBodyInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $goldenId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $partnerGoldenId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $experienceGoldenId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="8")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $type;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="9", max="12")
     * @Assert\Regex(pattern="/^[0-9]+$/")
     *
     * @JMS\Type("string")
     */
    public ?string $voucher = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="3", max="3")
     *
     * @JMS\Type("string")
     */
    public ?string $brand = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="2", max="2")
     *
     * @JMS\Type("string")
     */
    public ?string $country = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="10")
     *
     * @JMS\Type("string")
     */
    public ?string $requestType = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     *
     * @JMS\Type("string")
     */
    public ?string $channel = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     *
     * @JMS\Type("string")
     */
    public ?string $cancellationChannel = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="30")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $status;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_integer")
     */
    public int $totalPrice;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $startDate;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTime $endDate;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="254")
     *
     * @JMS\Type("string")
     */
    public ?string $customerComment = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="254")
     *
     * @JMS\Type("string")
     */
    public ?string $partnerComment = null;

    /**
     * @Assert\Type(type="array")
     * @Assert\NotBlank
     *
     * @JMS\Type("array<App\Contract\Request\Guest\GuestRequest>")
     *
     * @var array<int, GuestRequest>
     */
    public array $guest;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime")
     */
    public \DateTime $placedAt;

    /**
     * @Assert\Type(type="DateTime")
     *
     * @JMS\Type("DateTime")
     */
    public ?\DateTime $cancelledAt = null;
}
