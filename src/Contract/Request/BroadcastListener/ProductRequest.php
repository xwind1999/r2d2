<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Constraint\ProductTypeConstraint;
use App\Contract\Request\BroadcastListener\Product\Brand;
use App\Contract\Request\BroadcastListener\Product\Country;
use App\Contract\Request\BroadcastListener\Product\ListPrice;
use App\Contract\Request\BroadcastListener\Product\Partner;
use App\Contract\Request\BroadcastListener\Product\Universe;
use App\Entity\Component;
use App\Entity\ExperienceComponent;
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
     * @Assert\Length(max="255")
     * @Assert\NotNull
     *
     * @JMS\Type("string")
     */
    public string $name;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(max="255")
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
     */
    public bool $isSellable = false;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isReservable = false;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\Brand")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\Brand")
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
     */
    public ?Country $sellableCountry = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(choices=\App\Constraint\ProductStatusConstraint::VALID_VALUES)
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
     */
    public ?int $productPeopleNumber = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(choices=\App\Constraint\RoomStockTypeConstraint::VALID_VALUES)
     *
     * @JMS\Type("string")
     */
    public ?string $roomStockType = null;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     *
     * @JMS\Type("strict_integer")
     */
    public ?int $stockAllotment = null;

    /**
     * @Assert\Type(type="integer")
     * @Assert\PositiveOrZero
     *
     * @JMS\Type("strict_integer")
     */
    public ?int $productDuration = null;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice(choices=\App\Constraint\ProductDurationUnitConstraint::VALID_VALUES)
     *
     * @JMS\Type("string")
     */
    public ?string $productDurationUnit = null;

    /**
     * @Assert\Type(type="App\Contract\Request\BroadcastListener\Product\ListPrice")
     * @Assert\Valid
     *
     * @JMS\Type("App\Contract\Request\BroadcastListener\Product\ListPrice")
     * @JMS\SerializedName("listPrice")
     */
    public ?ListPrice $listPrice = null;

    public ?\DateTime $updatedAt = null;

    public static function fromArray(array $product): self
    {
        $productRequest = new self();
        $productRequest->id = $product['id'];
        $productRequest->name = $product['name'] ?? '';
        $productRequest->status = $product['status'];
        $productRequest->type = $product['type'];
        $productRequest->description = $product['description'] ?? null;

        if (!empty($product['roomStockType'])) {
            $productRequest->roomStockType = $product['roomStockType'];
        }

        if (!empty($product['productDurationUnit'])) {
            $productRequest->productDurationUnit = $product['productDurationUnit'];
        }

        if (!empty($product['productPeopleNumber'])) {
            $productRequest->productPeopleNumber = (int) $product['productPeopleNumber'];
        }

        if (!empty($product['stockAllotment'])) {
            $productRequest->stockAllotment = (int) $product['stockAllotment'];
        }

        if (!empty($product['productDuration'])) {
            $productRequest->productDuration = (int) $product['productDuration'];
        }

        if (!empty($product['sellableBrand'])) {
            $productRequest->sellableBrand = Brand::create($product['sellableBrand']);
        }

        if (!empty($product['sellableCountry'])) {
            $productRequest->sellableCountry = Country::create($product['sellableCountry']);
        }

        if (!empty($product['updatedAt'])) {
            $productRequest->updatedAt = new \DateTime($product['updatedAt']);
        }

        if (!empty($product['partner'])) {
            $productRequest->partner = Partner::create($product['partner']);
        }

        if (!empty($product['isSellable'])) {
            $productRequest->isSellable = (bool) $product['isSellable'];
        }

        if (!empty($product['isReservable'])) {
            $productRequest->isReservable = (bool) $product['isReservable'];
        }

        if (!empty($product['listPrice.amount']) && !empty($product['listPrice.currencyCode'])) {
            $productRequest->listPrice = ListPrice::createFromAmountAndCurrencyCode(
                $product['listPrice.amount'],
                $product['listPrice.currencyCode']
            );
        }

        return $productRequest;
    }

    public static function fromComponent(Component $component): self
    {
        $productRequest = new self();
        $productRequest->id = $component->goldenId;
        $productRequest->name = $component->name;
        $productRequest->status = $component->status;
        $productRequest->type = ProductTypeConstraint::COMPONENT;

        return $productRequest;
    }

    public static function fromExperienceComponent(ExperienceComponent $experienceComponent): self
    {
        $productRequest = new self();
        $productRequest->id = $experienceComponent->component->goldenId;
        $productRequest->name = $experienceComponent->component->name;
        $productRequest->status = $experienceComponent->experience->status;
        $productRequest->type = ProductTypeConstraint::COMPONENT;

        return $productRequest;
    }

    public static function fromBoxExperience(string $experienceGoldenId): self
    {
        $productRequest = new self();
        $productRequest->id = $experienceGoldenId;
        $productRequest->type = ProductTypeConstraint::EXPERIENCE;

        return $productRequest;
    }

    public static function fromBox(array $box): self
    {
        $productRequest = new self();
        $productRequest->id = $box['experienceGoldenId'];
        $productRequest->type = ProductTypeConstraint::EXPERIENCE;

        return $productRequest;
    }

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
            'product_duration' => $this->productDuration,
            'product_duration_unit' => $this->productDurationUnit,
            'room_stock_type' => $this->roomStockType,
            'stock_allotment' => $this->stockAllotment,
            'list_price' => $this->listPrice ? $this->listPrice->getContext() : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
        ];
    }
}
