<?php

declare(strict_types=1);

namespace App\Manager;

use App\Constraint\PartnerStatusConstraint;
use App\Constraint\ProductStatusConstraint;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\EAI\RoomRequest;
use App\Contract\Request\Internal\Component\ComponentCreateRequest;
use App\Contract\Request\Internal\Component\ComponentUpdateRequest;
use App\Entity\Component;
use App\Exception\Manager\Component\OutdatedComponentException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\ManageableProductNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Helper\Manageable\ManageableProductService;
use App\Repository\ComponentRepository;
use App\Repository\PartnerRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Messenger\MessageBusInterface;

class ComponentManager
{
    private ComponentRepository $repository;
    private PartnerRepository $partnerRepository;
    private ManageableProductService $manageableProductService;
    private PartnerManager $partnerManager;
    private MessageBusInterface $messageBus;

    public function __construct(
        ComponentRepository $repository,
        PartnerRepository $partnerRepository,
        ManageableProductService $manageableProductService,
        PartnerManager $partnerManager,
        MessageBusInterface $messageBus
    ) {
        $this->repository = $repository;
        $this->partnerRepository = $partnerRepository;
        $this->manageableProductService = $manageableProductService;
        $this->partnerManager = $partnerManager;
        $this->messageBus = $messageBus;
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
        try {
            $partner = $this->partnerRepository->findOneByGoldenId($productRequest->partner ? $productRequest->partner->id : '');
        } catch (PartnerNotFoundException $exception) {
            $partner = $this->partnerManager->createPlaceholder($productRequest->partner ? $productRequest->partner->id : '');
        }

        try {
            $component = $this->repository->findOneByGoldenId($productRequest->id);
        } catch (ComponentNotFoundException $exception) {
            $component = new Component();
        }

        if (!empty($component->externalUpdatedAt) && $component->externalUpdatedAt > $productRequest->updatedAt) {
            throw new OutdatedComponentException();
        }

        $currentEntity = clone $component;
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
        $component->duration = $productRequest->productDuration;
        $component->durationUnit = $productRequest->productDurationUnit;

        $this->repository->save($component);
        $this->manageableProductService->dispatchForComponent($productRequest, $currentEntity);
    }

    public function getRoomsByExperienceGoldenIdsList(array $experienceIds): array
    {
        return $this->repository->findRoomsByExperienceGoldenIdsList($experienceIds);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function calculateManageableFlag(string $componentGoldenId): void
    {
        try {
            $component = $this->repository->findComponentWithManageableCriteria(
                $this->createComponentRequiredCriteria($componentGoldenId, $this->createManageableCriteria())
            );
            if (false === $component->isManageable) {
                $component->isManageable = true;
                $this->repository->save($component);
            }
        } catch (ManageableProductNotFoundException $exception) {
            $component = $this->repository->findComponentWithManageableRelationships(
                $this->createComponentRequiredCriteria($componentGoldenId, Criteria::create())
            );
            if (true === $component->isManageable) {
                $component->isManageable = false;
                $this->repository->save($component);
            }
        }
        $this->messageBus->dispatch(RoomRequest::transformFromComponent($component));
    }

    private function createManageableCriteria(): Criteria
    {
        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq(
            'component.status',
            ProductStatusConstraint::PRODUCT_STATUS_ACTIVE
        ));
        $criteria->andWhere(
            Criteria::expr()->in(
                'box.status',
                [
                    ProductStatusConstraint::PRODUCT_STATUS_LIVE,
                    ProductStatusConstraint::PRODUCT_STATUS_REDEEMABLE,
                    ProductStatusConstraint::PRODUCT_STATUS_PRODUCTION,
                    ProductStatusConstraint::PRODUCT_STATUS_PROSPECT,
                    ProductStatusConstraint::PRODUCT_STATUS_READY,
                ]
            )
        );
        $criteria->andWhere(Criteria::expr()->eq(
            'experience.status',
            ProductStatusConstraint::PRODUCT_STATUS_ACTIVE
        ));
        $criteria->andWhere(Criteria::expr()->eq('component.isReservable', true));
        $criteria->andWhere(Criteria::expr()->eq('boxExperience.isEnabled', true));
        $criteria->andWhere(Criteria::expr()->eq('experienceComponent.isEnabled', true));
        $criteria->andWhere(Criteria::expr()->eq('partner.status', PartnerStatusConstraint::PARTNER_STATUS_PARTNER));

        /** @psalm-suppress TooManyArguments */
        $c = $criteria->andWhere(
            Criteria::expr()->orX(
                    Criteria::expr()->isNull('partner.ceaseDate'),
                    Criteria::expr()->gt('partner.ceaseDate', new \DateTime())
            )
        );

        return $criteria;
    }

    private function createComponentRequiredCriteria(string $componentGoldenId, Criteria $criteria): Criteria
    {
        return $criteria->andWhere(Criteria::expr()->eq('component.goldenId', $componentGoldenId));
    }
}
