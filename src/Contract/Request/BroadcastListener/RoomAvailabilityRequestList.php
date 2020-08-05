<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;

class RoomAvailabilityRequestList implements ValidatableRequest, RequestBodyInterface
{
    /**
     * @var RoomAvailabilityRequest[]
     *
     * @JMS\Type("array<App\Contract\Request\BroadcastListener\RoomAvailabilityRequest>")
     * @JMS\SerializedName("items")
     * @JMS\Inline()
     */
    public array $items;
}
