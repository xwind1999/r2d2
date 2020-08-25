<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\RoomPriceRequest;
use App\Entity\Component;
use App\Entity\RoomPrice;
use App\Exception\Manager\RoomPrice\OutdatedRoomPriceException;
use App\Repository\ComponentRepository;
use App\Repository\RoomPriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RoomPriceManager
{
    protected RoomPriceRepository $repository;

    protected ComponentRepository $componentRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        RoomPriceRepository $repository,
        ComponentRepository $componentRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->componentRepository = $componentRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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
        $dateTo = $roomPriceRequest->dateTo->setTime(0, 0, 0, 0)->modify('+1 day');

        $datePeriod = new \DatePeriod($dateFrom, new \DateInterval('P1D'), $dateTo);
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

    public function getRoomPricesByComponentAndDateRange(
        Component $component,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        return $this->repository->findByComponentAndDateRange($component, $dateFrom, $dateTo);
    }
}
