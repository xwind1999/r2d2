<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\ExperienceComponent\ExperienceComponentCreateRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentDeleteRequest;
use App\Contract\Request\ExperienceComponent\ExperienceComponentUpdateRequest;
use App\Contract\Response\ExperienceComponent\ExperienceComponentCreateResponse;
use App\Contract\Response\ExperienceComponent\ExperienceComponentUpdateResponse;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Manager\ExperienceComponent\RelationshipAlreadyExistsException;
use App\Exception\Repository\EntityNotFoundException;
use App\Exception\Repository\ExperienceComponentNotFoundException;
use App\Manager\ExperienceComponentManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExperienceComponentController
{
    /**
     * @Route("/api/experience-component", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="experience-component")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ExperienceComponentCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Relationship created",
     *     @Model(type=ExperienceComponentCreateResponse::class)
     * )
     * @SWG\Response(
     *     response=409,
     *     description="Relationship already exists"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Resource not found"
     * )
     */
    public function create(
        ExperienceComponentCreateRequest $experienceComponentCreateRequest,
        ExperienceComponentManager $experienceComponentManager
    ): ExperienceComponentCreateResponse {
        try {
            $experienceComponent = $experienceComponentManager->create($experienceComponentCreateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        } catch (RelationshipAlreadyExistsException $exception) {
            throw new ResourceConflictException();
        }

        return new ExperienceComponentCreateResponse($experienceComponent);
    }

    /**
     * @Route("/api/experience-component", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="experience-component")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ExperienceComponentDeleteRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Relationship deleted"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Resource not found"
     * )
     *
     * @throws ResourceNotFoundException
     */
    public function delete(
        ExperienceComponentDeleteRequest $experienceComponentDeleteRequest,
        ExperienceComponentManager $experienceComponentManager
    ): Response {
        try {
            $experienceComponentManager->delete($experienceComponentDeleteRequest);
        } catch (ExperienceComponentNotFoundException | EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/experience-component/", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="experience-component")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ExperienceComponentUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Relationship upated",
     *     @Model(type=ExperienceComponentUpdateResponse::class)
     * )
     *
     * @throws ResourceNotFoundException
     */
    public function put(
        ExperienceComponentUpdateRequest $experienceComponentUpdateRequest,
        ExperienceComponentManager $experienceComponentManager
    ): ExperienceComponentUpdateResponse {
        try {
            $experienceComponent = $experienceComponentManager->update($experienceComponentUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new ExperienceComponentUpdateResponse($experienceComponent);
    }
}
