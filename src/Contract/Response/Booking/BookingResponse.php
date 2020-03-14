<?php

declare(strict_types=1);

namespace App\Contract\Response\Booking;

use App\Contract\ResponseContract;
use App\Entity\Booking;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

abstract class BookingResponse extends ResponseContract
{
    public const HTTP_CODE = 200;

    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $uuid;

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
    public ?string $voucher;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="3", max="3")
     *
     * @JMS\Type("string")
     */
    public ?string $brand;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="2", max="2")
     *
     * @JMS\Type("string")
     */
    public ?string $country;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="10")
     *
     * @JMS\Type("string")
     */
    public ?string $requestType;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     *
     * @JMS\Type("string")
     */
    public ?string $channel;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     *
     * @JMS\Type("string")
     */
    public ?string $cancellationChannel;

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
     * @Assert\Length(min="1", max="45")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $customerExternalId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     */
    public ?string $customerFirstName;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     */
    public ?string $customerLastName;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     */
    public ?string $customerEmail;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
     *
     * @JMS\Type("string")
     */
    public ?string $customerPhone;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="1024")
     *
     * @JMS\Type("string")
     */
    public ?string $customerComment;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="1024")
     *
     * @JMS\Type("string")
     */
    public ?string $partnerComment;

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
    public ?\DateTime $cancelledAt;

    /**
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime")
     */
    public \DateTime $createdAt;

    /**
     * @Assert\NotBlank
     *
     * @JMS\Type("DateTime")
     */
    public \DateTime $updatedAt;

    public function __construct(Booking $booking)
    {
        $this->uuid = $booking->uuid->toString();
        $this->goldenId = $booking->goldenId;
        $this->partnerGoldenId = $booking->partnerGoldenId;
        $this->experienceGoldenId = $booking->experienceGoldenId;
        $this->type = $booking->type;
        $this->voucher = $booking->voucher;
        $this->brand = $booking->brand;
        $this->country = $booking->country;
        $this->requestType = $booking->requestType;
        $this->channel = $booking->channel;
        $this->cancellationChannel = $booking->cancellationChannel;
        $this->status = $booking->status;
        $this->totalPrice = $booking->totalPrice;
        $this->startDate = $booking->startDate;
        $this->endDate = $booking->endDate;
        $this->customerExternalId = $booking->customerExternalId;
        $this->customerFirstName = $booking->customerFirstName;
        $this->customerLastName = $booking->customerLastName;
        $this->customerEmail = $booking->customerEmail;
        $this->customerPhone = $booking->customerPhone;
        $this->customerComment = $booking->customerComment;
        $this->partnerComment = $booking->partnerComment;
        $this->placedAt = $booking->placedAt;
        $this->cancelledAt = $booking->cancelledAt;
        $this->createdAt = $booking->createdAt;
        $this->updatedAt = $booking->updatedAt;
    }
}
