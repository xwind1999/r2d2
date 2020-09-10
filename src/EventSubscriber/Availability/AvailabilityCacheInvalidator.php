<?php

declare(strict_types=1);

namespace App\EventSubscriber\Availability;

use App\Cache\QuickDataCache;
use App\Entity\Component;
use App\Event\Product\AvailabilityUpdatedEvent;
use App\Repository\Flat\FlatManageableComponentRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AvailabilityCacheInvalidator implements EventSubscriberInterface
{
    private QuickDataCache $quickDataCache;

    private FlatManageableComponentRepository $flatManageableComponentRepository;

    public function __construct(
        QuickDataCache $quickDataCache,
        FlatManageableComponentRepository $flatManageableComponentRepository
    ) {
        $this->quickDataCache = $quickDataCache;
        $this->flatManageableComponentRepository = $flatManageableComponentRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            AvailabilityUpdatedEvent::class => ['invalidateCache'],
        ];
    }

    public function invalidateCache(AvailabilityUpdatedEvent $event): void
    {
        $changedDates = $this->expandCacheDates($event->component, $event->dates);
        $boxes = $this->flatManageableComponentRepository->getBoxesByComponentId($event->component->goldenId);
        $keys = [];
        foreach ($changedDates as $date => $set) {
            foreach ($boxes as $box) {
                $keys[] = $this->quickDataCache->boxDateKey($box, $date);
            }
        }
        $this->quickDataCache->massInvalidate($keys);
    }

    private function expandCacheDates(Component $component, array $changedDates): array
    {
        if ($component->duration > 1) {
            $extraDates = [];
            foreach ($changedDates as $date => $set) {
                $beginDate = (new \DateTime($date))->modify(sprintf('-%s day', $component->duration - 1));
                $endDate = (new \DateTime($date))->modify(sprintf('+%s day', $component->duration));
                $interval = new \DateInterval('P1D');
                $period = new \DatePeriod($beginDate, $interval, $endDate);
                foreach ($period as $extraDate) {
                    $extraDates[$extraDate->format('Y-m-d')] = true;
                }
            }
            $changedDates += $extraDates;
        }

        return $changedDates;
    }
}
