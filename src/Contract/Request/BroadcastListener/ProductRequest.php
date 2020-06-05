<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\ListPrice;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Universe;
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
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $id;

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
    public ?string $description = null;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\Universe")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\Universe")
     */
    public ?Universe $universe = null;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     * @JMS\SerializedName("isSellable")
     */
    public bool $isSellable;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     * @JMS\SerializedName("isReservable")
     */
    public bool $isReservable;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\Brand")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\Brand")
     * @JMS\SerializedName("sellableBrand")
     */
    public ?Brand $sellableBrand = null;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\Partner")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\Partner")
     */
    public ?Partner $partner = null;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\Country")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\Country")
     * @JMS\SerializedName("sellableCountry")
     */
    public ?Country $sellableCountry = null;

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
     * @Assert\Type(type="integer")
     * @Assert\Length(min="1", max="2")
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("productPeopleNumber")
     */
    public ?int $productPeopleNumber = null;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     *
     * @JMS\Type("strict_integer")
     * @JMS\SerializedName("voucherExpirationDuration")
     */
    public ?int $voucherExpirationDuration = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="10")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("roomStockType")
     */
    public ?string $roomStockType = null;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     *
     * @JMS\Type("strict_integer")
     * @JMS\SerializedName("stockAllotment")
     */
    public ?int $stockAllotment = null;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\ListPrice")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\ListPrice")
     */
    public ?ListPrice $listPrice = null;

    public function getContext(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'universe' => $this->universe ? $this->universe->id : '',
            'is_sellable' => $this->isReservable,
            'is_reservable' => $this->isReservable,
            'partner' => $this->partner ? $this->partner->id : '',
            'sellable_brand' => $this->sellableBrand ? $this->sellableBrand->code : '',
            'sellable_country' => $this->sellableCountry ? $this->sellableCountry->code : '',
            'status' => $this->status,
            'type' => $this->type,
            'product_people_number' => $this->productPeopleNumber,
            'voucher_expiration_duration' => $this->voucherExpirationDuration,
            'room_stock_type' => $this->roomStockType,
            'stock_allotment' => $this->stockAllotment,
            'list_price' => $this->listPrice ? $this->listPrice->getContext() : null,
        ];
    }
}
