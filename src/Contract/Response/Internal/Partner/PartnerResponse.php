<?php

declare(strict_types=1);

namespace App\Contract\Response\Internal\Partner;

use App\Contract\ResponseContract;
use App\Entity\Partner;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

abstract class PartnerResponse extends ResponseContract
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
     * @Assert\Currency()
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
        $this->uuid = $partner->uuid->toString();
        $this->goldenId = $partner->goldenId;
        $this->status = $partner->status;
        $this->currency = $partner->currency;
        $this->ceaseDate = $partner->ceaseDate;
        $this->createdAt = $partner->createdAt;
        $this->updatedAt = $partner->updatedAt;
    }
}
