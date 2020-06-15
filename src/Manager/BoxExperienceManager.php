<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\ProductRelationshipRequest;
use App\Contract\Request\Internal\BoxExperience\BoxExperienceCreateRequest;
use App\Contract\Request\Internal\BoxExperience\BoxExperienceDeleteRequest;
use App\Entity\BoxExperience;
use App\Exception\Manager\BoxExperience\OutdatedBoxExperienceRelationshipException;
use App\Exception\Manager\BoxExperience\RelationshipAlreadyExistsException;
use App\Exception\Repository\BoxNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Repository\BoxExperienceRepository;
use App\Repository\BoxRepository;
use App\Repository\ExperienceRepository;

class BoxExperienceManager
{
    protected BoxRepository $boxRepository;

    protected ExperienceRepository $experienceRepository;

    protected BoxExperienceRepository $boxExperienceRepository;

    public function __construct(BoxExperienceRepository $boxExperienceRepository, BoxRepository $boxRepository, ExperienceRepository $experienceRepository)
    {
        $this->boxRepository = $boxRepository;
        $this->experienceRepository = $experienceRepository;
        $this->boxExperienceRepository = $boxExperienceRepository;
    }

    /**
     * @throws BoxNotFoundException
     * @throws ExperienceNotFoundException
     * @throws RelationshipAlreadyExistsException
     */
    public function create(BoxExperienceCreateRequest $boxExperienceCreateRequest): BoxExperience
    {
        $box = $this->boxRepository->findOneByGoldenId($boxExperienceCreateRequest->boxGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId($boxExperienceCreateRequest->experienceGoldenId);

        if ($this->boxExperienceRepository->findOneByBoxExperience($box, $experience)) {
            throw new RelationshipAlreadyExistsException();
        }

        $boxExperience = new BoxExperience();
        $boxExperience->box = $box;
        $boxExperience->boxGoldenId = $box->goldenId;
        $boxExperience->experience = $experience;
        $boxExperience->experienceGoldenId = $experience->goldenId;
        $boxExperience->isEnabled = $boxExperienceCreateRequest->isEnabled;
        $boxExperience->externalUpdatedAt = $boxExperienceCreateRequest->externalUpdatedAt;

        $this->boxExperienceRepository->save($boxExperience);

        return $boxExperience;
    }

    /**
     * @throws BoxNotFoundException
     * @throws ExperienceNotFoundException
     */
    public function delete(BoxExperienceDeleteRequest $boxExperienceDeleteRequest): void
    {
        $box = $this->boxRepository->findOneByGoldenId($boxExperienceDeleteRequest->boxGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId($boxExperienceDeleteRequest->experienceGoldenId);

        $boxExperience = $this->boxExperienceRepository->findOneByBoxExperience($box, $experience);

        if (!$boxExperience) {
            return;
        }

        $this->boxExperienceRepository->delete($boxExperience);
    }

    /**
     * @throws ExperienceNotFoundException
     * @throws BoxNotFoundException
     * @throws OutdatedBoxExperienceRelationshipException
     */
    public function replace(ProductRelationshipRequest $relationshipRequest): void
    {
        $box = $this->boxRepository->findOneByGoldenId($relationshipRequest->parentProduct);
        $experience = $this->experienceRepository->findOneByGoldenId($relationshipRequest->childProduct);
        $boxExperience = $this->boxExperienceRepository->findOneByBoxExperience($box, $experience);

        if (!empty($boxExperience->externalUpdatedAt) && $boxExperience->externalUpdatedAt > $relationshipRequest->updatedAt) {
            throw new OutdatedBoxExperienceRelationshipException();
        }

        $boxExperience = $boxExperience ?? new BoxExperience();
        $boxExperience->box = $box;
        $boxExperience->boxGoldenId = $box->goldenId;
        $boxExperience->experience = $experience;
        $boxExperience->isEnabled = $relationshipRequest->isEnabled;
        $boxExperience->experienceGoldenId = $experience->goldenId;
        $boxExperience->externalUpdatedAt = $relationshipRequest->updatedAt;

        $this->boxExperienceRepository->save($boxExperience);
    }
}
