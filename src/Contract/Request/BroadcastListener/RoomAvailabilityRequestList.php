<?php

declare(strict_types=1);

namespace App\Contract\Request\BroadcastListener;

use App\Helper\Request\RequestBodyInterface;
use App\Helper\Request\ValidatableRequest;
use Clogger\ContextualInterface;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RoomAvailabilityRequestList implements ValidatableRequest, RequestBodyInterface, ContextualInterface
{
    /**
     * @var RoomAvailabilityRequest[]
     *
     * @Assert\Valid
     *
     * @JMS\Type("array<App\Contract\Request\BroadcastListener\RoomAvailabilityRequest>")
     * @JMS\SerializedName("items")
     * @JMS\Inline()
     */
    public array $items;

    /**
     * @codeCoverageIgnore
     */
    public function getContext(): array
    {
        return [
            'items' => $this->items,
        ];
    }
}
