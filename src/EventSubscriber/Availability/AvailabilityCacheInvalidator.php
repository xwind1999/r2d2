<?php

declare(strict_types=1);

namespace App\EventSubscriber\Availability;

use App\Cache\QuickDataCache;
use App\Event\Product\AvailabilityUpdatedEvent;
use App\Helper\DateDurationExpander;
use App\Repository\Flat\FlatManageableComponentRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AvailabilityCacheInvalidator implements EventSubscriberInterface
{
    private QuickDataCache $quickDataCache;
    private FlatManageableComponentRepository $flatManageableComponentRepository;
    private DateDurationExpander $dateDurationExpander;

    public function __construct(
        QuickDataCache $quickDataCache,
        FlatManageableComponentRepository $flatManageableComponentRepository,
        DateDurationExpander $dateDurationExpander
    ) {
        $this->quickDataCache = $quickDataCache;
        $this->flatManageableComponentRepository = $flatManageableComponentRepository;
        $this->dateDurationExpander = $dateDurationExpander;
    }

    public static function getSubscribedEvents()
    {
        return [
            AvailabilityUpdatedEvent::class => ['invalidateCache'],
        ];
    }

    public function invalidateCache(AvailabilityUpdatedEvent $event): void
    {
        $changedDates = $this->dateDurationExpander->expandDatesForComponentDuration($event->component, $event->dates);
        $boxes = $this->flatManageableComponentRepository->getBoxesByComponentId($event->component->goldenId);
        $keys = [];
        foreach ($changedDates as $date => $set) {
            foreach ($boxes as $box) {
                $keys[] = $this->quickDataCache->boxDateKey($box, $date);
            }
        }
        $this->quickDataCache->massInvalidate($keys);
    }
}
