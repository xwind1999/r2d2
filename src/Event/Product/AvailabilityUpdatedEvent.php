<?php

declare(strict_types=1);

namespace App\Event\Product;

use App\Entity\Component;
use Symfony\Contracts\EventDispatcher\Event;

class AvailabilityUpdatedEvent extends Event
{
    public Component $component;

    /**
     * @var array<string, \DateTime>
     */
    public array $dates;

    /**
     * @param array<string, \DateTime> $dates
     */
    public function __construct(Component $component, array $dates)
    {
        $this->component = $component;
        $this->dates = $dates;
    }
}
