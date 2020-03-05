<?php

declare(strict_types=1);

namespace App\Contract\Response\Partner;

use App\Contract\ResponseContract;
use App\Entity\Partner;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PartnerGetResponse extends ResponseContract
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
     * @Assert\Length(min="1", max="8")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $status;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="3", max="3")
     * @Assert\NotBlank
     *
     * @JMS\Type("string")
     */
    public string $currency;

    /**
     * @JMS\Type("DateTime")
     */
    public ?\DateTime $ceaseDate;

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

    public function __construct(Partner $partner)
    {
        /*
         * @todo remove verification after issue is fixed on doctrine
         * @see https://github.com/doctrine/orm/issues/7999
         */
        if ($partner->uuid instanceof UuidInterface) {
            $this->uuid = $partner->uuid->toString();
        } else {
            $this->uuid = '';
        }

        $this->goldenId = $partner->goldenId;
        $this->status = $partner->status;
        $this->currency = $partner->currency;
        $this->ceaseDate = $partner->ceaseDate;
        $this->createdAt = $partner->createdAt;
        $this->updatedAt = $partner->updatedAt;
    }
}
