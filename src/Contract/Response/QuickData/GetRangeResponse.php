<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class GetRangeResponse extends QuickDataResponse
{
    /**
     * @Assert\Type(type="array<App\Contract\Response\QuickData\GetRange\Package>")
     *
     * @JMS\Type("array<App\Contract\Response\QuickData\GetRange\Package>")
     * @JMS\SerializedName("PackagesList")
     */
    public array $packagesList = [];
}
