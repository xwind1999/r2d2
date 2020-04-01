<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class GetPackageResponse extends QuickDataResponse
{
    /**
     * @Assert\Type(type="array<App\Contract\Response\QuickData\GetPackage\ListPrestation>")
     *
     * @JMS\Type("array<App\Contract\Response\QuickData\GetPackage\ListPrestation>")
     * @JMS\SerializedName("ListPrestation")
     */
    public array $listPrestation;
}
