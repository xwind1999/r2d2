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
     * @Assert\Length(min="1", max="8")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $type;

    public function getContext(): array
    {
        return [
            'goldenId' => $this->goldenId,
            'name' => $this->name,
            'description' => $this->description,
            'universe' => $this->universe,
            'isSellable' => $this->isReservable,
            'isReservable' => $this->isReservable,
            'partnerGoldenId' => $this->partnerGoldenId,
            'brand' => $this->brand,
            'country' => $this->country,
            'status' => $this->status,
            'type' => $this->type,
        ];
    }
}
