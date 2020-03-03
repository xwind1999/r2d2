<?php

declare(strict_types=1);

namespace App\Contract\Response\Room;

use App\Contract\ResponseContract;
use App\Entity\Room;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RoomGetResponse extends ResponseContract
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

    public function __construct(Room $room)
    {
        /*
         * @todo remove verification after issue is fixed on doctrine
         * @see https://github.com/doctrine/orm/issues/7999
         */
        if ($room->uuid instanceof UuidInterface) {
            $this->uuid = $room->uuid->toString();
        } else {
            $this->uuid = '';
        }

        $this->goldenId = $room->goldenId;
        $this->partnerGoldenId = $room->partnerGoldenId;
        $this->name = $room->name;
        $this->description = $room->description;
        $this->inventory = $room->inventory;
        $this->isSellable = $room->isSellable;
        $this->status = $room->status;
        $this->createdAt = $room->createdAt;
        $this->updatedAt = $room->updatedAt;
    }
}
