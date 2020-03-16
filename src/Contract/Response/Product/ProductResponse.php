<?php

declare(strict_types=1);

namespace App\Contract\Response\Product;

use App\Contract\Request\Product\ProductCreateRequest;
use App\Contract\ResponseContract;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ProductResponse extends ResponseContract
{
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
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isSellable;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotBlank
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

    public function __construct(ProductCreateRequest $productCreateRequest)
    {
        $this->uuid = $productCreateRequest->uuid;
        $this->goldenId = $productCreateRequest->goldenId;
        $this->name = $productCreateRequest->name;
        $this->description = $productCreateRequest->description;
        $this->universe = $productCreateRequest->universe;
        $this->isSellable = $productCreateRequest->isSellable;
        $this->isReservable = $productCreateRequest->isReservable;
        $this->partnerGoldenId = $productCreateRequest->partnerGoldenId;
    }
}
