<?php

declare(strict_types=1);

namespace App\Manager;

use App\Constraint\ProductStatusConstraint;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Internal\Component\ComponentCreateRequest;
use App\Contract\Request\Internal\Component\ComponentUpdateRequest;
use App\Contract\Request\Manageable\ManageableProductRequest;
use App\Entity\Component;
use App\Exception\Manager\Component\OutdatedComponentException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Helper\Manageable\ManageableProductService;
use App\Repository\ComponentRepository;
use App\Repository\PartnerRepository;
use Doctrine\ORM\NonUniqueResultException;

class ComponentManager
{
    private ComponentRepository $repository;
    private PartnerRepository $partnerRepository;
    private ManageableProductService $manageableProductService;

    public function __construct(
        ComponentRepository $repository,
        PartnerRepository $partnerRepository,
        ManageableProductService $manageableProductService
    ) {
        $this->repository = $repository;
        $this->partnerRepository = $partnerRepository;
        $this->manageableProductService = $manageableProductService;
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
        $component->durationUnit = $componentCreateRequest->durationUnit;
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
        $component->duration = $componentUpdateRequest->duration;
        $component->durationUnit = $componentUpdateRequest->durationUnit;
        $component->inventory = $componentUpdateRequest->inventory;
        $component->isSellable = $componentUpdateRequest->isSellable;
        $component->isReservable = $componentUpdateRequest->isReservable;
        $component->status = $componentUpdateRequest->status;

        $this->repository->save($component);

        return $component;
    }

    /**
     * @throws PartnerNotFoundException
     * @throws OutdatedComponentException
     */
    public function replace(ProductRequest $productRequest): void
    {
        $partner = $this->partnerRepository->findOneByGoldenId($productRequest->partner ? $productRequest->partner->id : '');

        try {
            $component = $this->repository->findOneByGoldenId($productRequest->id);
        } catch (ComponentNotFoundException $exception) {
            $component = new Component();
        }

        if (!empty($component->externalUpdatedAt) && $component->externalUpdatedAt > $productRequest->updatedAt) {
            throw new OutdatedComponentException();
        }

        $componentStatus = '';
        if (!empty($component->status)) {
            $componentStatus = $component->status;
        }
        $component->goldenId = $productRequest->id;
        $component->partner = $partner;
        $component->partnerGoldenId = $productRequest->partner ? $productRequest->partner->id : '';
        $component->name = $productRequest->name;
        $component->description = $productRequest->description;
        $component->isReservable = $productRequest->isReservable;
        $component->isSellable = $productRequest->isSellable;
        $component->status = $productRequest->status;
        $component->roomStockType = $productRequest->roomStockType;
        $component->inventory = $productRequest->stockAllotment;
        $component->externalUpdatedAt = $productRequest->updatedAt;

        $this->repository->save($component);
        $this->manageableProductService->dispatchForProduct($productRequest, $componentStatus);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findAndSetManageableComponent(ManageableProductRequest $manageableProductRequest): void
    {
        $component = $this->repository->findComponentWithBoxExperienceAndRelationship($manageableProductRequest);
        $component[0]->isManageable = $this->isManageable($component);
        $this->repository->save($component[0]);
    }

    private function isManageable(array $component): bool
    {
        return
            ProductStatusConstraint::PRODUCT_STATUS_ACTIVE === $component['componentStatus']
            && ProductStatusConstraint::PRODUCT_STATUS_ACTIVE === $component['boxStatus']
            && true === $component['componentReservable']
            && true === $component['boxExperienceStatus']
            && true === $component['experienceComponentStatus']
            && false === $component[0]->isManageable;
    }

    public function getRoomsByExperienceGoldenIdsList(array $experienceIds): array
    {
        return $this->repository->findRoomsByExperienceGoldenIdsList($experienceIds);
    }
}
