<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\PriceInformationRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\Internal\Experience\ExperienceCreateRequest;
use App\Contract\Request\Internal\Experience\ExperienceUpdateRequest;
use App\Entity\Experience;
use App\Exception\Manager\Experience\OutdatedExperienceException;
use App\Exception\Manager\Experience\OutdatedExperiencePriceException;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Exception\Repository\PartnerNotFoundException;
use App\Helper\Manageable\ManageableProductService;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;

class ExperienceManager
{
    private ExperienceRepository $repository;
    private PartnerRepository $partnerRepository;
    private ManageableProductService $manageableProductService;
    private PartnerManager $partnerManager;

    public function __construct(
        ExperienceRepository $repository,
        PartnerRepository $partnerRepository,
        ManageableProductService $manageableProductService,
        PartnerManager $partnerManager
    ) {
        $this->repository = $repository;
        $this->partnerRepository = $partnerRepository;
        $this->manageableProductService = $manageableProductService;
        $this->partnerManager = $partnerManager;
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
        $experience->status = $experienceCreateRequest->status;
        $experience->price = $experienceCreateRequest->price;
        $experience->priceUpdatedAt = new \DateTime('now');

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
    public function getOneByGoldenId(string $goldenId): Experience
    {
        return $this->repository->findOneByGoldenId($goldenId);
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
        $experience->status = $experienceUpdateRequest->status;

        $this->repository->save($experience);

        return $experience;
    }

    /**
     * @throws PartnerNotFoundException
     */
    public function replace(ProductRequest $productRequest): void
    {
        try {
            $partner = $this->partnerRepository->findOneByGoldenId($productRequest->partner ? $productRequest->partner->id : '');
        } catch (PartnerNotFoundException $exception) {
            $partner = $this->partnerManager->createPlaceholder($productRequest->partner ? $productRequest->partner->id : '');
        }

        try {
            $experience = $this->repository->findOneByGoldenId($productRequest->id);
        } catch (ExperienceNotFoundException $exception) {
            $experience = new Experience();
        }

        if (!empty($experience->externalUpdatedAt) && $experience->externalUpdatedAt > $productRequest->updatedAt) {
            throw new OutdatedExperienceException();
        }

        $currentEntity = clone $experience;
        $experience->goldenId = $productRequest->id;
        $experience->partner = $partner;
        $experience->partnerGoldenId = $productRequest->partner ? $productRequest->partner->id : '';
        $experience->name = $productRequest->name;
        $experience->description = $productRequest->description ?? '';
        $experience->peopleNumber = $productRequest->productPeopleNumber;
        $experience->status = $productRequest->status;
        $experience->externalUpdatedAt = $productRequest->updatedAt;

        $this->repository->save($experience);
        $this->manageableProductService->dispatchForExperience($productRequest, $currentEntity);
    }

    /**
     * @throws ExperienceNotFoundException
     * @throws OutdatedExperiencePriceException
     */
    public function insertPriceInfo(PriceInformationRequest $priceInformationRequest): void
    {
        $experience = $this->repository->findOneByGoldenId($priceInformationRequest->product->id);

        if (!empty($experience->priceUpdatedAt) && $experience->priceUpdatedAt > $priceInformationRequest->updatedAt) {
            throw new OutdatedExperiencePriceException();
        }

        $experience->price = $priceInformationRequest->averageValue ? $priceInformationRequest->averageValue->amount : null;
        $experience->commissionType = $priceInformationRequest->averageCommissionType;
        $experience->commission = $priceInformationRequest->averageCommission;
        $experience->priceUpdatedAt = $priceInformationRequest->updatedAt;

        $this->repository->save($experience);
    }
}
