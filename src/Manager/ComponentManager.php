<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Internal\Component\ComponentCreateRequest;
use App\Contract\Request\Internal\Component\ComponentUpdateRequest;
use App\Entity\Component;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Repository\ComponentRepository;
use App\Repository\PartnerRepository;

class ComponentManager
{
    protected ComponentRepository $repository;

    protected PartnerRepository $partnerRepository;

    public function __construct(ComponentRepository $repository, PartnerRepository $partnerRepository)
    {
        $this->repository = $repository;
        $this->partnerRepository = $partnerRepository;
    }

    public function create(ComponentCreateRequest $componentCreateRequest): Component
    {
        $partner = $this->partnerRepository->findOneByGoldenId($componentCreateRequest->partnerGoldenId);

        $component = new Component();
        $component->partner = $partner;
        $component->goldenId = $componentCreateRequest->goldenId;
        $component->partnerGoldenId = $componentCreateRequest->partnerGoldenId;
        $component->name = $componentCreateRequest->name;
        $component->description = $componentCreateRequest->description;
        $component->inventory = $componentCreateRequest->inventory;
        $component->duration = $componentCreateRequest->duration;
        $component->isSellable = $componentCreateRequest->isSellable;
        $component->isReservable = $componentCreateRequest->isReservable;
        $component->status = $componentCreateRequest->status;

        $this->repository->save($component);

        return $component;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): Component
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $component = $this->get($uuid);
        $this->repository->delete($component);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, ComponentUpdateRequest $componentUpdateRequest): Component
    {
        $partner = $this->partnerRepository->findOneByGoldenId($componentUpdateRequest->partnerGoldenId);

        $component = $this->get($uuid);
        $component->partner = $partner;
        $component->goldenId = $componentUpdateRequest->goldenId;
        $component->partnerGoldenId = $componentUpdateRequest->partnerGoldenId;
        $component->name = $componentUpdateRequest->name;
        $component->description = $componentUpdateRequest->description;
        $component->inventory = $componentUpdateRequest->inventory;
        $component->duration = $componentUpdateRequest->voucherExpirationDuration;
        $component->isSellable = $componentUpdateRequest->isSellable;
        $component->isReservable = $componentUpdateRequest->isReservable;
        $component->status = $componentUpdateRequest->status;

        $this->repository->save($component);

        return $component;
    }

    /**
     * @throws PartnerNotFoundException
     */
    public function replace(ProductRequest $productRequest): void
    {
        $partner = $this->partnerRepository->findOneByGoldenId($productRequest->partner ? $productRequest->partner->id : '');

        try {
            $component = $this->repository->findOneByGoldenId($productRequest->id);
        } catch (ComponentNotFoundException $exception) {
            $component = new Component();
        }

        $component->goldenId = $productRequest->id;
        $component->partner = $partner;
        $component->partnerGoldenId = $productRequest->partner ? $productRequest->partner->id : '';
        $component->name = $productRequest->name;
        $component->description = $productRequest->description;
        $component->duration = $productRequest->voucherExpirationDuration;
        $component->isReservable = $productRequest->isReservable;
        $component->isSellable = $productRequest->isSellable;
        $component->status = $productRequest->status;
        $component->roomStockType = $productRequest->roomStockType;
        $component->inventory = $productRequest->stockAllotment;

        $this->repository->save($component);
    }
}
