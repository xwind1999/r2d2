<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class GetPackageV2Response extends QuickDataResponse
{
    /**
     * @Assert\Type(type="array<App\Contract\Response\QuickData\GetPackageV2\Package>")
     *
     * @JMS\Type("array<App\Contract\Response\QuickData\GetPackageV2\Package>")
     * @JMS\SerializedName("ListPackage")
     */
    public array $listPackage = [];
}
