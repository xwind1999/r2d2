<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentCreateRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentDeleteRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentUpdateRequest;
use App\Entity\ExperienceComponent;
use App\Exception\Manager\ExperienceComponent\RelationshipAlreadyExistsException;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\ExperienceComponentNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Repository\ComponentRepository;
use App\Repository\ExperienceComponentRepository;
use App\Repository\ExperienceRepository;

class ExperienceComponentManager
{
    protected ComponentRepository $componentRepository;

    protected ExperienceRepository $experienceRepository;

    protected ExperienceComponentRepository $experienceComponentRepository;

    public function __construct(
        ExperienceComponentRepository $experienceComponentRepository,
        ComponentRepository $componentRepository,
        ExperienceRepository $experienceRepository
    ) {
        $this->componentRepository = $componentRepository;
        $this->experienceRepository = $experienceRepository;
        $this->experienceComponentRepository = $experienceComponentRepository;
    }

    /**
     * @throws ExperienceComponentNotFoundException
     * @throws ExperienceNotFoundException
     * @throws RelationshipAlreadyExistsException
     * @throws ComponentNotFoundException
     */
    public function create(ExperienceComponentCreateRequest $experienceComponentCreateRequestComponent): ExperienceComponent
    {
        $component = $this->componentRepository->findOneByGoldenId($experienceComponentCreateRequestComponent->componentGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId(
            $experienceComponentCreateRequestComponent->experienceGoldenId
        );

        if ($this->experienceComponentRepository->findOneByExperienceComponent($experience, $component)) {
            throw new RelationshipAlreadyExistsException();
        }

        $experienceComponent = new ExperienceComponent();
        $experienceComponent->component = $component;
        $experienceComponent->componentGoldenId = $component->goldenId;
        $experienceComponent->experience = $experience;
        $experienceComponent->experienceGoldenId = $experience->goldenId;
        $experienceComponent->isEnabled = $experienceComponentCreateRequestComponent->isEnabled;
        $experienceComponent->externalUpdatedAt = $experienceComponentCreateRequestComponent->externalUpdatedAt;

        $this->experienceComponentRepository->save($experienceComponent);

        return $experienceComponent;
    }

    /**
     * @throws ExperienceNotFoundException
     * @throws ComponentNotFoundException
     * @throws ExperienceComponentNotFoundException
     */
    public function delete(ExperienceComponentDeleteRequest $experienceDeleteRequestComponent): void
    {
        $component = $this->componentRepository->findOneByGoldenId($experienceDeleteRequestComponent->componentGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId(
            $experienceDeleteRequestComponent->experienceGoldenId
        );

        $experienceComponent = $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component);

        if (!$experienceComponent) {
            return;
        }

        $this->experienceComponentRepository->delete($experienceComponent);
    }

    /**
     * @throws ExperienceComponentNotFoundException
     * @throws ExperienceNotFoundException
     * @throws ComponentNotFoundException
     */
    public function update(ExperienceComponentUpdateRequest $experienceComponentUpdateRequest): ExperienceComponent
    {
        $component = $this->componentRepository->findOneByGoldenId($experienceComponentUpdateRequest->componentGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId($experienceComponentUpdateRequest->experienceGoldenId);
        $experienceComponent = $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component);

        if (null === $experienceComponent) {
            throw new ExperienceComponentNotFoundException();
        }

        $experienceComponent->component = $component;
        $experienceComponent->componentGoldenId = $component->goldenId;
        $experienceComponent->experience = $experience;
        $experienceComponent->experienceGoldenId = $experience->goldenId;
        $experienceComponent->isEnabled = $experienceComponentUpdateRequest->isEnabled;
        $experienceComponent->externalUpdatedAt = $experienceComponentUpdateRequest->externalUpdatedAt;

        $this->experienceComponentRepository->save($experienceComponent);

        return $experienceComponent;
    }

    /**
     * @throws ExperienceNotFoundException
     * @throws ComponentNotFoundException
     */
    public function replace(ProductRelationshipRequest $relationshipRequest): void
    {
        $component = $this->componentRepository->findOneByGoldenId($relationshipRequest->childProduct);
        $experience = $this->experienceRepository->findOneByGoldenId($relationshipRequest->parentProduct);
        $experienceComponent = $this->experienceComponentRepository->findOneByExperienceComponent($experience, $component);

        $experienceComponent = $experienceComponent ?? new ExperienceComponent();
        $experienceComponent->component = $component;
        $experienceComponent->componentGoldenId = $component->goldenId;
        $experienceComponent->experience = $experience;
        $experienceComponent->experienceGoldenId = $experience->goldenId;
        $experienceComponent->isEnabled = $relationshipRequest->isEnabled;
        $experienceComponent->externalUpdatedAt = new \DateTime();

        $this->experienceComponentRepository->save($experienceComponent);
    }
}
