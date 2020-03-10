<?php

declare(strict_types=1);

namespace App\Contract\Response\Box;

use App\Contract\ResponseContract;
use App\Entity\Box;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BoxGetResponse extends ResponseContract
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
     * @Assert\Length(min="3", max="3")
     *
     * @JMS\Type("string")
     */
    public ?string $brand;

    /**
     * @Assert\Length(min="2", max="2")
     *
     * @JMS\Type("string")
     */
    public ?string $country;

    /**
     * @Assert\Length(min="1", max="10")
     *
     * @JMS\Type("string")
     */
    public ?string $status;

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

    public function __construct(Box $box)
    {
        /*
         * @todo remove verification after issue is fixed on doctrine
         * @see https://github.com/doctrine/orm/issues/7999
         */
        if ($box->uuid instanceof UuidInterface) {
            $this->uuid = $box->uuid->toString();
        } else {
            $this->uuid = '';
        }

        $this->goldenId = $box->goldenId;
        $this->status = $box->status;
        $this->brand = $box->brand;
        $this->country = $box->country;
        $this->createdAt = $box->createdAt;
        $this->updatedAt = $box->updatedAt;
    }
}
