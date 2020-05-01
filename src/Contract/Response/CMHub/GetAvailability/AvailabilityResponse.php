<?php

declare(strict_types=1);

namespace App\Contract\Response\CMHub\GetAvailability;

use App\Contract\Response\CMHub\CMHubResponse;
use App\Contract\Response\CMHub\GetAvailabilityResponse;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class AvailabilityResponse extends CMHubResponse
{
    /**
     * @Assert\Type(type="array<App\Contract\Response\CMHub\GetAvailabilityResponse>")
     *
     * @JMS\Type("array<App\Contract\Response\CMHub\GetAvailabilityResponse>")
     *
     * @var array<GetAvailabilityResponse>
     */
    public $availability = [];

    /**
     * @param array<GetAvailabilityResponse> $availability
     */
    public function __construct(array $availability)
    {
        $this->availability = $availability;
    }
}
