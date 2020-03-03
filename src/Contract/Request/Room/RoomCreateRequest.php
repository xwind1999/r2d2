<?php

declare(strict_types=1);

namespace App\Contract\Request\Room;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RoomCreateRequest implements RequestBodyInterface, ValidatableRequest
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
     * @Assert\Length(min="1", max="255")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $name;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $description;

    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     *
     * @JMS\Type("strict_integer")
     */
    public int $inventory;

    /**
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     *
     * @JMS\Type("strict_boolean")
     */
    public bool $isSellable;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="8")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $status;
}
