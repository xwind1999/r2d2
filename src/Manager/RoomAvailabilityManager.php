<?php

declare(strict_types=1);

namespace App\Manager;

use App\Constants\DateTimeConstants;
use App\Constraint\RoomStockTypeConstraint;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequest;
use App\Contract\Request\BroadcastListener\RoomAvailabilityRequestList;
use App\Entity\Booking;
use App\Entity\RoomAvailability;
use App\Event\Product\AvailabilityUpdatedEvent;
use App\Exception\Manager\RoomAvailability\OutdatedRoomAvailabilityInformationException;
use App\Helper\DateTimeHelper;
use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RoomAvailabilityManager
{
    private const LOG_MESSAGE_AVAILABILITY_UNKNOWN_COMPONENT = 'Received room availability for unknown component';

    protected RoomAvailabilityRepository $repository;
    protected ComponentRepository $componentRepository;

    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RoomAvailabilityRepository $repository,
        ComponentRepository $componentRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->componentRepository = $componentRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->messageBus = $messageBus;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getRoomAvailabilitiesByBoxId(
        string $boxId,
        \DateTimeInterface $startDate
    ): array {
        return $this->repository->findAvailableRoomsByBoxId($boxId, $startDate);
    }

    public function getRoomAvailabilitiesByMultipleExperienceGoldenIds(
        array $experienceGoldenIds,
        \DateTimeInterface $startDate
    ): array {
        return $this->repository->findAvailableRoomsByMultipleExperienceIds($experienceGoldenIds, $startDate);
    }

    /**
     * @throws \Exception
     */
    public function replace(RoomAvailabilityRequest $roomAvailabilityRequest): void
    {
        $datePeriod = DateTimeHelper::createDatePeriod(
            $roomAvailabilityRequest->dateFrom,
            $roomAvailabilityRequest->dateTo
        );

        $component = $this->componentRepository->findOneByGoldenId($roomAvailabilityRequest->product->id);

        if ($this->canRoomAvailabilityBeDeleted($roomAvailabilityRequest)) {
            $this->repository->deleteByComponentIdAndDateRange(
                $component,
                $roomAvailabilityRequest->dateFrom,
                $roomAvailabilityRequest->dateTo
            );

            return;
        }

        $roomAvailabilityList = $this->repository->findByComponentAndDateRange(
            $component,
            $roomAvailabilityRequest->dateFrom,
            $roomAvailabilityRequest->dateTo
        );

        $changedDates = [];

        /** @var \DateTime $date */
        foreach ($datePeriod as $date) {
            $newAvailability = true;
            $roomAvailability = new RoomAvailability();

            if (isset($roomAvailabilityList[$date->format(DateTimeConstants::DEFAULT_DATE_FORMAT)])) {
                $roomAvailability = $roomAvailabilityList[$date->format(DateTimeConstants::DEFAULT_DATE_FORMAT)];
                $newAvailability = false;
            }

            if ($roomAvailability->externalUpdatedAt &&
                $roomAvailabilityRequest->updatedAt < $roomAvailability->externalUpdatedAt) {
                $this->logger->warning(OutdatedRoomAvailabilityInformationException::class, $roomAvailabilityRequest->getContext());
                continue;
            }

            if (true === $newAvailability || $this->hasAvailabilityChangedForBoxCache($roomAvailabilityRequest, $roomAvailability)) {
                $changedDates[$date->format(DateTimeConstants::DEFAULT_DATE_FORMAT)] = $date;
            }

            $roomAvailability->componentGoldenId = $roomAvailabilityRequest->product->id;
            $roomAvailability->component = $component;
            $roomAvailability->stock = $roomAvailabilityRequest->quantity;
            $roomAvailability->date = $date;
            $roomAvailability->isStopSale = $roomAvailabilityRequest->isStopSale;
            $roomAvailability->externalUpdatedAt = $roomAvailabilityRequest->updatedAt ?? null;

            $this->entityManager->persist($roomAvailability);
//            $this->eventDispatcher->dispatch(new AvailabilityUpdatedEvent($component, $changedDates));
        }
        $this->entityManager->flush();
    }

    public function dispatchRoomAvailabilitiesRequest(RoomAvailabilityRequestList $roomAvailabilityRequestList): void
    {
        $componentIds = [];
        foreach ($roomAvailabilityRequestList->items as $roomAvailabilityRequest) {
            $componentIds[] = $roomAvailabilityRequest->product->id;
        }

        $existingComponents = $this->componentRepository->filterManageableComponetsByComponentId($componentIds);

        foreach ($roomAvailabilityRequestList->items as $roomAvailabilityRequest) {
            if (!isset($existingComponents[$roomAvailabilityRequest->product->id])) {
                $this->logger->warning(
                    static::LOG_MESSAGE_AVAILABILITY_UNKNOWN_COMPONENT,
                    $roomAvailabilityRequest->getContext()
                );
                continue;
            }

            $this->messageBus->dispatch($roomAvailabilityRequest);
        }
    }

    private function hasAvailabilityChangedForBoxCache(
        RoomAvailabilityRequest $roomAvailabilityRequest,
        RoomAvailability $roomAvailability
    ): bool {
        $hasStopSaleChanged = $roomAvailability->isStopSale !== $roomAvailabilityRequest->isStopSale;

        $hasStockChanged = $roomAvailability->stock !== $roomAvailabilityRequest->quantity;

        $hasAvailabilityChanged =
            $hasStockChanged
            && (
                0 === $roomAvailabilityRequest->quantity
                || 0 === $roomAvailability->stock
            );

        return $hasStopSaleChanged || $hasAvailabilityChanged;
    }

    public function updateStockBookingConfirmation(Booking $booking): void
    {
        $bookingDates = $booking->bookingDate->getValues();
        $componentId = $bookingDates[0]->componentGoldenId;
        $component = $this->componentRepository->findOneByGoldenId($componentId);

        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST === $component->roomStockType) {
            return;
        }
        $dates = [];

        foreach ($bookingDates as $bookingDate) {
            $dates[] = $bookingDate->date->format(DateTimeConstants::DEFAULT_DATE_FORMAT);
        }
        $stockCounts = array_count_values($dates);
        $dates = array_unique($dates);
        $bookedRoomsTotal = $stockCounts[$dates[0]];

        $roomAvailabilities = $this->repository->findAllByComponentGoldenIdAndDates(
            $componentId,
            $dates
        );

        if (empty($roomAvailabilities)) {
            return;
        }

        foreach ($dates as $key => $date) {
            if (!isset($roomAvailabilities[$date])) {
                unset($dates[$key]);
                continue;
            }
            $roomAvailability = $roomAvailabilities[$date];
            if ($bookedRoomsTotal >= $roomAvailability->stock) {
                $this->entityManager->remove($roomAvailability);
                unset($dates[$key]);
            }
        }
        $this->entityManager->flush();

        if (!empty($dates)) {
            $this->repository->updateStocksForAvailability(
                $componentId,
                $dates,
                $bookedRoomsTotal
            );
        }
    }

    public function updateStockBookingCancellation(Booking $booking): void
    {
        $bookingDates = $booking->bookingDate->getValues();
        $componentId = $bookingDates[0]->componentGoldenId;
        $component = $this->componentRepository->findOneByGoldenId($componentId);

        if (RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST === $component->roomStockType) {
            return;
        }
        $dates = [];

        foreach ($bookingDates as $bookingDate) {
            $dates[] = $bookingDate->date->format(DateTimeConstants::DEFAULT_DATE_FORMAT);
        }

        $stockCounts = array_count_values($dates);
        $dates = array_unique($dates);
        $bookedRoomsTotal = $stockCounts[$dates[0]];

        $roomAvailabilities = $this->repository->findAllByComponentGoldenIdAndDates(
            $componentId,
            $dates
        );

        foreach ($dates as $key => $date) {
            if (!isset($roomAvailabilities[$date])) {
                $roomAvailability = new RoomAvailability();
                $roomAvailability->stock = $bookedRoomsTotal;
                $roomAvailability->componentGoldenId = $componentId;
                $roomAvailability->date = new \DateTime($date);
                $roomAvailability->component = $component;
                $this->entityManager->persist($roomAvailability);
                unset($dates[$key]);
            }
        }
        $this->entityManager->flush();

        if (!empty($dates)) {
            $this->repository->updateStocksForAvailability(
                $componentId,
                $dates,
                -$bookedRoomsTotal
            );
        }
    }

    public function getRoomAndPriceAvailabilitiesByExperienceIdAndDates(
        string $experienceId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        return $this->repository->findAvailableRoomsAndPricesByExperienceIdAndDates(
            $experienceId,
            $startDate,
            $endDate
        );
    }

    private function canRoomAvailabilityBeDeleted(RoomAvailabilityRequest $roomAvailabilityRequest): bool
    {
        return 0 === $roomAvailabilityRequest->quantity && false === $roomAvailabilityRequest->isStopSale;
    }
}
