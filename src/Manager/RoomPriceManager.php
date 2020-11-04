<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Contract\Request\BroadcastListener\RoomPriceRequestList;
use App\Entity\RoomPrice;
use App\Exception\Manager\RoomPrice\OutdatedRoomPriceException;
use App\Helper\DateTimeHelper;
use App\Repository\ComponentRepository;
use App\Repository\RoomPriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class RoomPriceManager
{
    private const LOG_MESSAGE_PRICE_UNKNOWN_COMPONENT = 'Received room price for unknown component';

    protected RoomPriceRepository $repository;

    protected ComponentRepository $componentRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;

    public function __construct(
        RoomPriceRepository $repository,
        ComponentRepository $componentRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->componentRepository = $componentRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->messageBus = $messageBus;
    }

    public function replace(RoomPriceRequest $roomPriceRequest): void
    {
        $component = $this->componentRepository->findOneByGoldenId($roomPriceRequest->product->id);

        $roomPrices = $this->repository->findByComponentAndDateRange(
            $component,
            $roomPriceRequest->dateFrom,
            $roomPriceRequest->dateTo
        );

        $dateFrom = $roomPriceRequest->dateFrom->setTime(0, 0, 0, 0);
        $dateTo = $roomPriceRequest->dateTo->setTime(0, 0, 0, 0);

        $datePeriod = DateTimeHelper::createDatePeriod($dateFrom, $dateTo);
        foreach ($datePeriod as $date) {
            $dateString = $date->format('Y-m-d');

            if (!isset($roomPrices[$dateString])) {
                $roomPrices[$dateString] = new RoomPrice();
            }

            if (
                !empty($roomPrices[$dateString]->externalUpdatedAt)
                && $roomPrices[$dateString]->externalUpdatedAt > $roomPriceRequest->updatedAt
            ) {
                $this->logger->warning(OutdatedRoomPriceException::MESSAGE, $roomPriceRequest->getContext());

                continue;
            }

            $roomPrices[$dateString]->componentGoldenId = $roomPriceRequest->product->id;
            $roomPrices[$dateString]->component = $component;
            $roomPrices[$dateString]->date = $date;
            $roomPrices[$dateString]->price = $roomPriceRequest->price->amount;
            $roomPrices[$dateString]->externalUpdatedAt = $roomPriceRequest->updatedAt;

            $this->entityManager->persist($roomPrices[$dateString]);
        }

        $this->entityManager->flush();
    }

    public function dispatchRoomPricesRequest(RoomPriceRequestList $roomPriceRequestList): void
    {
        $componentIds = [];
        foreach ($roomPriceRequestList->items as $roomPriceRequest) {
            $componentIds[] = $roomPriceRequest->product->id;
        }

        $existingComponents = $this->componentRepository->filterManageableComponetsByComponentId($componentIds);

        foreach ($roomPriceRequestList->items as $roomPriceRequest) {
            if (!isset($existingComponents[$roomPriceRequest->product->id])) {
                $this->logger->warning(static::LOG_MESSAGE_PRICE_UNKNOWN_COMPONENT, $roomPriceRequest->getContext());
                continue;
            }
            $this->messageBus->dispatch($roomPriceRequest);
        }
    }
}
