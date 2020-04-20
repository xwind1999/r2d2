<?php

declare(strict_types=1);

namespace App\Contract\Response\Experience;

use App\Contract\ResponseContract;
use App\Entity\Experience;
use App\Helper\TimestampableEntityTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

abstract class ExperienceResponse extends ResponseContract
{
    use TimestampableEntityTrait;

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
     *
     * @JMS\Type("string")
     */
    public string $name;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1")
     *
     * @JMS\Type("string")
     */
    public string $description;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min="1", max="2")
     *
     * @JMS\Type("string")
     */
    public ?string $productPeopleNumber;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("strict_integer")
     */
    public ?int $voucherExpirationDuration;

    public function __construct(Experience $experience)
    {
        $this->uuid = $experience->uuid->toString();
        $this->goldenId = $experience->goldenId;
        $this->partnerGoldenId = $experience->partnerGoldenId;
        $this->name = $experience->name;
        $this->description = $experience->description;
        $this->productPeopleNumber = $experience->peopleNumber;
        $this->voucherExpirationDuration = $experience->duration;
        $this->createdAt = $experience->createdAt;
        $this->updatedAt = $experience->updatedAt;
    }
}
