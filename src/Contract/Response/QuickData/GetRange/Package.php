<?php

declare(strict_types=1);

namespace App\Contract\Response\QuickData\GetRange;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class Package
{
    /**
     * @Assert\Type(type="string")
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("Package")
     */
    public string $package;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("Stock")
     */
    public int $stock;

    /**
     * @Assert\Type(type="integer")
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("Request")
     */
    public int $request;
}
