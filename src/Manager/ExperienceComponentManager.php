<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\Request\BroadcastListener\RelationshipRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentCreateRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentDeleteRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentUpdateRequest;
use App\Entity\ExperienceComponent;
use App\Exception\Manager\ExperienceComponent\RelationshipAlreadyExistsException;
use App\Exception\Repository\ExperienceComponentNotFoundException;
use App\Exception\Repository\ExperienceNotFoundException;
use App\Exception\Repository\RoomNotFoundException;
use App\Repository\ExperienceComponentRepository;
use App\Repository\ExperienceRepository;
use App\Repository\RoomRepository;

class ExperienceComponentManager
{
    protected RoomRepository $roomRepository;

    protected ExperienceRepository $experienceRepository;

    protected ExperienceComponentRepository $experienceComponentRepository;

    public function __construct(
        ExperienceComponentRepository $experienceComponentRepository,
        RoomRepository $roomRepository,
        ExperienceRepository $experienceRepository
    ) {
        $this->roomRepository = $roomRepository;
        $this->experienceRepository = $experienceRepository;
        $this->experienceComponentRepository = $experienceComponentRepository;
    }

    /**
     * @throws ExperienceComponentNotFoundException
     * @throws ExperienceNotFoundException
     * @throws RelationshipAlreadyExistsException
     * @throws RoomNotFoundException
     */
    public function create(ExperienceComponentCreateRequest $experienceComponentCreateRequestComponent): ExperienceComponent
    {
        $room = $this->roomRepository->findOneByGoldenId($experienceComponentCreateRequestComponent->roomGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId(
            $experienceComponentCreateRequestComponent->experienceGoldenId
        );

        if ($this->experienceComponentRepository->findOneByExperienceComponent($experience, $room)) {
            throw new RelationshipAlreadyExistsException();
        }

        $experienceComponent = new ExperienceComponent();
        $experienceComponent->room = $room;
        $experienceComponent->roomGoldenId = $room->goldenId;
        $experienceComponent->experience = $experience;
        $experienceComponent->experienceGoldenId = $experience->goldenId;
        $experienceComponent->isEnabled = $experienceComponentCreateRequestComponent->isEnabled;
        $experienceComponent->externalUpdatedAt = $experienceComponentCreateRequestComponent->externalUpdatedAt;

        $this->experienceComponentRepository->save($experienceComponent);

        return $experienceComponent;
    }

    /**
     * @throws ExperienceNotFoundException
     * @throws RoomNotFoundException
     * @throws ExperienceComponentNotFoundException
     */
    public function delete(ExperienceComponentDeleteRequest $experienceDeleteRequestComponent): void
    {
        $room = $this->roomRepository->findOneByGoldenId($experienceDeleteRequestComponent->roomGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId(
            $experienceDeleteRequestComponent->experienceGoldenId
        );

        $experienceComponent = $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room);

        if (!$experienceComponent) {
            return;
        }

        $this->experienceComponentRepository->delete($experienceComponent);
    }

    /**
     * @throws ExperienceComponentNotFoundException
     * @throws ExperienceNotFoundException
     * @throws RoomNotFoundException
     */
    public function update(ExperienceComponentUpdateRequest $experienceComponentUpdateRequest): ExperienceComponent
    {
        $room = $this->roomRepository->findOneByGoldenId($experienceComponentUpdateRequest->roomGoldenId);
        $experience = $this->experienceRepository->findOneByGoldenId($experienceComponentUpdateRequest->experienceGoldenId);
        $experienceComponent = $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room);

        if (null === $experienceComponent) {
            throw new ExperienceComponentNotFoundException();
        }

        $experienceComponent->room = $room;
        $experienceComponent->roomGoldenId = $room->goldenId;
        $experienceComponent->experience = $experience;
        $experienceComponent->experienceGoldenId = $experience->goldenId;
        $experienceComponent->isEnabled = $experienceComponentUpdateRequest->isEnabled;
        $experienceComponent->externalUpdatedAt = $experienceComponentUpdateRequest->externalUpdatedAt;

        $this->experienceComponentRepository->save($experienceComponent);

        return $experienceComponent;
    }

    /**
     * @throws ExperienceNotFoundException
     * @throws RoomNotFoundException
     */
    public function replace(RelationshipRequest $relationshipRequest): void
    {
        $room = $this->roomRepository->findOneByGoldenId($relationshipRequest->childProduct);
        $experience = $this->experienceRepository->findOneByGoldenId($relationshipRequest->parentProduct);
        $experienceComponent = $this->experienceComponentRepository->findOneByExperienceComponent($experience, $room);

        $experienceComponent = $experienceComponent ?? new ExperienceComponent();
        $experienceComponent->room = $room;
        $experienceComponent->roomGoldenId = $room->goldenId;
        $experienceComponent->experience = $experience;
        $experienceComponent->experienceGoldenId = $experience->goldenId;
        $experienceComponent->isEnabled = $relationshipRequest->isEnabled;
        $experienceComponent->externalUpdatedAt = new \DateTime();

        $this->experienceComponentRepository->save($experienceComponent);
    }
}
