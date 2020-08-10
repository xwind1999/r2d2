<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use JMS\Serializer\Annotation as JMS;

class RoomPriceRequestList implements ValidatableRequest, RequestBodyInterface
{
    /**
     * @var RoomPriceRequest[]
     *
     * @JMS\Type("array<App\Contract\Request\BroadcastListener\RoomPriceRequest>")
     * @JMS\SerializedName("items")
     * @JMS\Inline()
     */
    public array $items;
}
