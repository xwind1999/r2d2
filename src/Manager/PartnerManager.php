<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\Internal\Partner\PartnerCreateRequest;
use App\Contract\Request\Internal\Partner\PartnerUpdateRequest;
use App\Entity\Partner;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Repository\PartnerRepository;

class PartnerManager
{
    protected PartnerRepository $repository;

    public function __construct(PartnerRepository $repository)
    {
        $this->repository = $repository;
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

        $partner->goldenId = $partnerRequest->id;
        $partner->status = $partnerRequest->status;
        $partner->currency = $partnerRequest->currencyCode;
        $partner->isChannelManagerActive = $partnerRequest->isChannelManagerEnabled;
        $partner->ceaseDate = $partnerRequest->partnerCeaseDate;

        $this->repository->save($partner);
    }
}
