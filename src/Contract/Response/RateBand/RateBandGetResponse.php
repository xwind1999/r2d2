<?php

declare(strict_types=1);

namespace App\Contract\Response\RateBand;

use App\Contract\ResponseContract;
use App\Entity\RateBand;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RateBandGetResponse extends ResponseContract
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

    public function __construct(RateBand $rateBand)
    {
        /*
         * @todo remove verification after issue is fixed on doctrine
         * @see https://github.com/doctrine/orm/issues/7999
         */
        if ($rateBand->uuid instanceof UuidInterface) {
            $this->uuid = $rateBand->uuid->toString();
        } else {
            $this->uuid = '';
        }

        $this->goldenId = $rateBand->goldenId;
        $this->partnerGoldenId = $rateBand->partnerGoldenId;
        $this->name = $rateBand->name;
        $this->createdAt = $rateBand->createdAt;
        $this->updatedAt = $rateBand->updatedAt;
    }
}
