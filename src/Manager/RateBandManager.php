<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\RateBand\RateBandCreateRequest;
use App\Contract\Request\RateBand\RateBandUpdateRequest;
use App\Entity\RateBand;
use App\Exception\Repository\EntityNotFoundException;
use App\Repository\PartnerRepository;
use App\Repository\RateBandRepository;

class RateBandManager
{
    protected RateBandRepository $repository;

    protected PartnerRepository $partnerRepository;

    public function __construct(RateBandRepository $repository, PartnerRepository $partnerRepository)
    {
        $this->repository = $repository;
        $this->partnerRepository = $partnerRepository;
    }

    public function create(RateBandCreateRequest $rateBandCreateRequest): RateBand
    {
        $partner = $this->partnerRepository->findOneByGoldenId($rateBandCreateRequest->partnerGoldenId);

        $rateBand = new RateBand();
        $rateBand->partner = $partner;

        $rateBand->goldenId = $rateBandCreateRequest->goldenId;
        $rateBand->partnerGoldenId = $rateBandCreateRequest->partnerGoldenId;
        $rateBand->name = $rateBandCreateRequest->name;

        $this->repository->save($rateBand);

        return $rateBand;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): RateBand
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $rateBand = $this->get($uuid);
        $this->repository->delete($rateBand);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, RateBandUpdateRequest $rateBandUpdateRequest): RateBand
    {
        $partner = $this->partnerRepository->findOneByGoldenId($rateBandUpdateRequest->partnerGoldenId);

        $rateBand = $this->get($uuid);

        $rateBand->partner = $partner;
        $rateBand->goldenId = $rateBandUpdateRequest->goldenId;
        $rateBand->partnerGoldenId = $rateBandUpdateRequest->partnerGoldenId;
        $rateBand->name = $rateBandUpdateRequest->name;

        $this->repository->save($rateBand);

        return $rateBand;
    }
}
