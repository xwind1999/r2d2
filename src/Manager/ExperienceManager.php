<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Experience\ExperienceCreateRequest;
use App\Contract\Request\Experience\ExperienceUpdateRequest;
use App\Entity\Experience;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;

class ExperienceManager
{
    protected ExperienceRepository $repository;

    protected PartnerRepository $partnerRepository;

    public function __construct(ExperienceRepository $repository, PartnerRepository $partnerRepository)
    {
        $this->repository = $repository;
        $this->partnerRepository = $partnerRepository;
    }

    public function create(ExperienceCreateRequest $experienceCreateRequest): Experience
    {
        $partner = $this->partnerRepository->findOneByGoldenId($experienceCreateRequest->partnerGoldenId);

        $experience = new Experience();
        $experience->partner = $partner;
        $experience->goldenId = $experienceCreateRequest->goldenId;
        $experience->partnerGoldenId = $experienceCreateRequest->partnerGoldenId;
        $experience->name = $experienceCreateRequest->name;
        $experience->description = $experienceCreateRequest->description;
        $experience->peopleNumber = $experienceCreateRequest->productPeopleNumber;
        $experience->duration = $experienceCreateRequest->voucherExpirationDuration;

        $this->repository->save($experience);

        return $experience;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function get(string $uuid): Experience
    {
        return $this->repository->findOne($uuid);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $uuid): void
    {
        $experience = $this->get($uuid);
        $this->repository->delete($experience);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function update(string $uuid, ExperienceUpdateRequest $experienceUpdateRequest): Experience
    {
        $partner = $this->partnerRepository->findOneByGoldenId($experienceUpdateRequest->partnerGoldenId);

        $experience = $this->get($uuid);
        $experience->partner = $partner;
        $experience->goldenId = $experienceUpdateRequest->goldenId;
        $experience->partnerGoldenId = $experienceUpdateRequest->partnerGoldenId;
        $experience->name = $experienceUpdateRequest->name;
        $experience->description = $experienceUpdateRequest->description;
        $experience->peopleNumber = $experienceUpdateRequest->productPeopleNumber;
        $experience->duration = $experienceUpdateRequest->voucherExpirationDuration;

        $this->repository->save($experience);

        return $experience;
    }

    /**
     * @throws PartnerNotFoundException
     */
    public function replace(ProductRequest $productRequest): void
    {
        $partner = $this->partnerRepository->findOneByGoldenId($productRequest->partner ? $productRequest->partner->id : '');

        try {
            $experience = $this->repository->findOneByGoldenId($productRequest->id);
        } catch (ExperienceNotFoundException $exception) {
            $experience = new Experience();
        }

        $experience->goldenId = $productRequest->id;
        $experience->partner = $partner;
        $experience->partnerGoldenId = $productRequest->partner ? $productRequest->partner->id : '';
        $experience->name = $productRequest->name;
        $experience->description = $productRequest->description;
        $experience->peopleNumber = $productRequest->productPeopleNumber;
        $experience->duration = $productRequest->voucherExpirationDuration;

        $this->repository->save($experience);
    }
}
