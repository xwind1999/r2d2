<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData\GetPackageV2;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Package
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\Positive()
     * @Assert\NotBlank
     *
     * @JMS\SerializedName("PackageCode")
     * @JMS\Type("integer")
     */
    public int $packageCode;

    /**
     * @Assert\Type(type="array<App\Contract\Response\QuickData\GetPackage\ListPrestation>")
     *
     * @JMS\Type("array<App\Contract\Response\QuickData\GetPackage\ListPrestation>")
     * @JMS\SerializedName("ListPrestation")
     */
    public array $listPrestation;
}
