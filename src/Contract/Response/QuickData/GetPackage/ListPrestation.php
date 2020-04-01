<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData\GetPackage;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ListPrestation
{
    /**
     * @Assert\Type(type="array")
     *
     * @JMS\Type("array")
     * @JMS\SerializedName("Availabilities")
     */
    public array $availabilities;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("PrestId")
     */
    public int $prestId;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("Duration")
     */
    public int $duration;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("LiheId")
     */
    public int $liheId;

    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("PartnerCode")
     */
    public string $partnerCode;

    /**
     * @Assert\Type(type="boolean")
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("ExtraNight")
     */
    public bool $extraNight;

    /**
     * @Assert\Type(type="boolean")
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("ExtraRoom")
     */
    public bool $extraRoom;
}
