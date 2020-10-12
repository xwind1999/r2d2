<?php

declare(strict_types=1);

namespace App\Controller\Internal;

use App\Contract\Request\Internal\Experience\ExperienceCreateRequest;
use App\Contract\Request\Internal\Experience\ExperienceUpdateRequest;
use App\Contract\Response\Internal\Experience\ExperienceCreateResponse;
use App\Contract\Response\Internal\Experience\ExperienceGetResponse;
use App\Contract\Response\Internal\Experience\ExperienceUpdateResponse;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\ExperienceManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExperienceController
{
    /**
     * @Route("/internal/experience", methods={"POST"}, format="json")
     *
     * @OA\Tag(name="experience")
     * @OA\Parameter(
     *         name="body",
     *         in="query",
     *         @Model(type=ExperienceCreateRequest::class)
     * )
     * @OA\Response(
     *     response=201,
     *     description="Experience created",
     *     @Model(type=ExperienceCreateResponse::class)
     * )
     * @Security(name="basic")
     */
    public function create(
        ExperienceCreateRequest $experienceCreateRequest,
        ExperienceManager $experienceManager
    ): ExperienceCreateResponse {
        try {
            $experience = $experienceManager->create($experienceCreateRequest);
        } catch (UniqueConstraintViolationException $exception) {
            throw ResourceConflictException::forContext([], $exception);
        }

        return new ExperienceCreateResponse($experience);
    }

    /**
     * @Route("/internal/experience/{uuid}", methods={"GET"}, format="json")
     *
     * @OA\Tag(name="experience")
     * @OA\Parameter(
     *     name="uuid",
     *     in="path",
     *     @OA\Schema(
     *         type="string",
     *         format="uuid"
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Experience successfully retrieved",
     *     @Model(type=ExperienceGetResponse::class)
     * )
     * @Security(name="basic")
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(UuidInterface $uuid, ExperienceManager $experienceManager): ExperienceGetResponse
    {
        try {
            $experience = $experienceManager->get($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new ExperienceGetResponse($experience);
    }

    /**
     * @Route("/internal/experience/{uuid}", methods={"DELETE"}, format="json")
     *
     * @OA\Tag(name="experience")
     * @OA\Parameter(
     *     name="uuid",
     *     in="path",
     *     @OA\Schema(
     *         type="string",
     *         format="uuid"
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Experience deleted"
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(UuidInterface $uuid, ExperienceManager $experienceManager): Response
    {
        try {
            $experienceManager->delete($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/internal/experience/{uuid}", methods={"PUT"}, format="json")
     *
     * @OA\Tag(name="experience")
     * @OA\Parameter(
     *     name="uuid",
     *     in="path",
     *     @OA\Schema(
     *         type="string",
     *         format="uuid"
     *     )
     * )
     * @OA\Parameter(
     *         name="body",
     *         in="query",
     *         @Model(type=ExperienceUpdateRequest::class)
     * )
     * @OA\Response(
     *     response=200,
     *     description="Experience updated",
     *     @Model(type=ExperienceUpdateResponse::class)
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(
        UuidInterface $uuid,
        ExperienceUpdateRequest $experienceUpdateRequest,
        ExperienceManager $experienceManager
    ): ExperienceUpdateResponse {
        try {
            $experience = $experienceManager->update($uuid->toString(), $experienceUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new ExperienceUpdateResponse($experience);
    }
}
