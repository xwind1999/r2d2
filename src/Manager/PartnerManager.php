<?php

declare(strict_types=1);

namespace App\Manager;

use App\Constraint\PartnerStatusConstraint;
use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\Internal\Partner\PartnerCreateRequest;
use App\Contract\Request\Internal\Partner\PartnerUpdateRequest;
use App\Entity\Partner;
use App\Exception\Manager\Partner\OutdatedPartnerException;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Helper\Manageable\ManageableProductService;
use App\Repository\PartnerRepository;

class PartnerManager
{
    private PartnerRepository $repository;
    private ManageableProductService $manageableProductService;

    public function __construct(
        PartnerRepository $repository,
        ManageableProductService $manageableProductService
    ) {
        $this->repository = $repository;
        $this->manageableProductService = $manageableProductService;
    }

    public function create(PartnerCreateRequest $partnerCreateRequest): Partner
    {
        $partner = new Partner();
        $partner->goldenId = $partnerCreateRequest->goldenId;
        $partner->status = $partnerCreateRequest->status;
        $partner->currency = $partnerCreateRequest->currency;
        $partner->isChannelManagerActive = $partnerCreateRequest->isChannelManagerActive;
        $partner->ceaseDate = $partnerCreateRequest->ceaseDate;

        $this->repository->save($partner);

        return $partner;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): Partner
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getOneByGoldenId(string $goldenId): Partner
    {
        return $this->repository->findOneByGoldenId($goldenId);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $partner = $this->get($uuid);
        $this->repository->delete($partner);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, PartnerUpdateRequest $partnerUpdateRequest): Partner
    {
        $partner = $this->get($uuid);

        $partner->goldenId = $partnerUpdateRequest->goldenId;
        $partner->status = $partnerUpdateRequest->status;
        $partner->currency = $partnerUpdateRequest->currency;
        $partner->isChannelManagerActive = $partnerUpdateRequest->isChannelManagerActive;
        $partner->ceaseDate = $partnerUpdateRequest->ceaseDate;

        $this->repository->save($partner);

        return $partner;
    }

    public function replace(PartnerRequest $partnerRequest): void
    {
        try {
            $partner = $this->repository->findOneByGoldenId($partnerRequest->id);
        } catch (PartnerNotFoundException $exception) {
            $partner = new Partner();
        }

        if (!empty($partner->externalUpdatedAt) && $partner->externalUpdatedAt > $partnerRequest->updatedAt) {
            throw new OutdatedPartnerException();
        }

        $currentEntity = clone $partner;
        $partner->goldenId = $partnerRequest->id;
        $partner->status = $partnerRequest->status;
        $partner->currency = $partnerRequest->currencyCode;
        $partner->isChannelManagerActive = $partnerRequest->isChannelManagerEnabled;
        $partner->ceaseDate = $partnerRequest->partnerCeaseDate;
        $partner->externalUpdatedAt = $partnerRequest->updatedAt;

        $this->repository->save($partner);
        $this->manageableProductService->dispatchForPartner($partnerRequest, $currentEntity);
    }

    public function createPlaceholder(string $goldenId): Partner
    {
        $partner = new Partner();
        $partner->goldenId = $goldenId;
        $partner->status = PartnerStatusConstraint::PARTNER_STATUS_PLACEHOLDER;
        $partner->currency = '';
        $partner->isChannelManagerActive = false;

        $this->repository->save($partner);

        return $partner;
    }
}
