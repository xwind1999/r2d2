<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProductRequest implements RequestBodyInterface, ValidatableRequest, ContextualInterface
{
    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="45")
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
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     */
    public string $name;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     */
    public string $description;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="255")
     *
     * @JMS\Type("string")
     */
    public string $universe;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isSellable;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isReservable;

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
     * @Assert\Length(min="3", max="3")
     *
     * @JMS\Type("string")
     */
    public string $brand;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="2", max="2")
     *
     * @JMS\Type("string")
     */
    public string $country;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="30")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $status;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="10")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $type;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="2")
     *
     * @JMS\Type("string")
     */
    public ?string $productPeopleNumber = null;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     *
     * @JMS\Type("strict_integer")
     */
    public ?int $voucherExpirationDuration = null;

    public function getContext(): array
    {
        return [
            'golden_id' => $this->goldenId,
            'name' => $this->name,
            'description' => $this->description,
            'universe' => $this->universe,
            'is_sellable' => $this->isReservable,
            'is_reservable' => $this->isReservable,
            'partner_golden_id' => $this->partnerGoldenId,
            'brand' => $this->brand,
            'country' => $this->country,
            'status' => $this->status,
            'type' => $this->type,
            'product_people_number' => $this->productPeopleNumber,
            'voucher_expiration_duration' => $this->voucherExpirationDuration,
        ];
    }
}
