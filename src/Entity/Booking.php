<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 */
class Booking
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid_binary_ordered_time", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     *
     * @CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
     */
    private UuidInterface $uuid;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private string $goldenId;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private string $partnerGoldenId;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private string $experienceGoldenId;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private string $voucher;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private string $brand;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private string $country;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private string $requestType;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private string $channel;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private string $cancellationChannel;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private string $status;

    /**
     * @ORM\Column(type="integer")
     */
    private int $totalPrice;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTime $startDate;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTime $endDate;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private string $customerExternalId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $customerFirstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $customerLastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $customerEmail;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private string $customerPhone;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private string $customerComment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private string $partnerComment;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTime $placedAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private \DateTime $cancelledAt;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTime $updatedAt;

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getGoldenId(): string
    {
        return $this->goldenId;
    }

    public function setGoldenId(string $goldenId): void
    {
        $this->goldenId = $goldenId;
    }

    public function getPartnerGoldenId(): string
    {
        return $this->partnerGoldenId;
    }

    public function setPartnerGoldenId(string $partnerGoldenId): void
    {
        $this->partnerGoldenId = $partnerGoldenId;
    }

    public function getExperienceGoldenId(): string
    {
        return $this->experienceGoldenId;
    }

    public function setExperienceGoldenId(string $experienceGoldenId): void
    {
        $this->experienceGoldenId = $experienceGoldenId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getVoucher(): string
    {
        return $this->voucher;
    }

    public function setVoucher(string $voucher): void
    {
        $this->voucher = $voucher;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getRequestType(): string
    {
        return $this->requestType;
    }

    public function setRequestType(string $requestType): void
    {
        $this->requestType = $requestType;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    public function getCancellationChannel(): string
    {
        return $this->cancellationChannel;
    }

    public function setCancellationChannel(string $cancellationChannel): void
    {
        $this->cancellationChannel = $cancellationChannel;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getTotalPrice(): int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(int $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getCustomerExternalId(): string
    {
        return $this->customerExternalId;
    }

    public function setCustomerExternalId(string $customerExternalId): void
    {
        $this->customerExternalId = $customerExternalId;
    }

    public function getCustomerFirstName(): string
    {
        return $this->customerFirstName;
    }

    public function setCustomerFirstName(string $customerFirstName): void
    {
        $this->customerFirstName = $customerFirstName;
    }

    public function getCustomerLastName(): string
    {
        return $this->customerLastName;
    }

    public function setCustomerLastName(string $customerLastName): void
    {
        $this->customerLastName = $customerLastName;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): void
    {
        $this->customerEmail = $customerEmail;
    }

    public function getCustomerPhone(): string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(string $customerPhone): void
    {
        $this->customerPhone = $customerPhone;
    }

    public function getCustomerComment(): string
    {
        return $this->customerComment;
    }

    public function setCustomerComment(string $customerComment): void
    {
        $this->customerComment = $customerComment;
    }

    public function getPartnerComment(): string
    {
        return $this->partnerComment;
    }

    public function setPartnerComment(string $partnerComment): void
    {
        $this->partnerComment = $partnerComment;
    }

    /**
     * @return mixed
     */
    public function getPlacedAt()
    {
        return $this->placedAt;
    }

    /**
     * @param mixed $placedAt
     */
    public function setPlacedAt($placedAt): void
    {
        $this->placedAt = $placedAt;
    }

    /**
     * @return mixed
     */
    public function getCancelledAt()
    {
        return $this->cancelledAt;
    }

    /**
     * @param mixed $cancelledAt
     */
    public function setCancelledAt($cancelledAt): void
    {
        $this->cancelledAt = $cancelledAt;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
