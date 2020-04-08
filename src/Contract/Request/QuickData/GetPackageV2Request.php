<?php

declare(strict_types=1);

namespace App\Contract\Request\QuickData;

use App\Helper\Request\RequestQueryInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class GetPackageV2Request implements RequestQueryInterface, ValidatableRequest
{
    /**
     * @Assert\Type(type="array")
     * @Assert\Positive()
     * @Assert\NotBlank
     *
     * @JMS\SerializedName("ListPackageCode")
     * @JMS\Type("csv")
     */
    public array $listPackageCode;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\SerializedName("dateFrom")
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTimeInterface $dateFrom;

    /**
     * @Assert\Type(type="DateTime")
     * @Assert\NotBlank
     *
     * @JMS\SerializedName("dateTo")
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    public \DateTimeInterface $dateTo;
}
