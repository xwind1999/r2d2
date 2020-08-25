<?php

declare(strict_types=1);

namespace App\Contract\Request\QuickData;

use App\Helper\Request\RequestQueryInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AvailabilityPricePeriodRequest implements RequestQueryInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     *
     * @JMS\SerializedName("ExperienceId")
     * @JMS\Type("string")
     */
    public string $experienceId;

    /**
     * @Assert\Type(type="integer")
     * @Assert\Positive()
     * @Assert\NotBlank
     *
     * @JMS\SerializedName("prestid")
     * @JMS\Type("integer")
     */
    public int $prestId;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\SerializedName("datefrom")
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTimeInterface $dateFrom;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\SerializedName("dateto")
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTimeInterface $dateTo;
}
