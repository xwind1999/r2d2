<?php

declare(strict_types=1);

namespace App\Controller\Internal;

use App\Contract\Request\Internal\BoxExperience\BoxExperienceCreateRequest;
use App\Contract\Request\Internal\BoxExperience\BoxExperienceDeleteRequest;
use App\Contract\Response\Internal\BoxExperience\BoxExperienceCreateResponse;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Manager\BoxExperience\RelationshipAlreadyExistsException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BoxExperienceManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BoxExperienceController
{
    /**
     * @Route("/internal/box-experience", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="box-experience")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BoxExperienceCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Relationship created",
     *     @Model(type=BoxExperienceCreateResponse::class)
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
    public function create(BoxExperienceCreateRequest $boxExperienceCreateRequest, BoxExperienceManager $boxExperienceManager): BoxExperienceCreateResponse
    {
        try {
            $boxExperience = $boxExperienceManager->create($boxExperienceCreateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        } catch (RelationshipAlreadyExistsException $exception) {
            throw new ResourceConflictException();
        }

        return new BoxExperienceCreateResponse($boxExperience);
    }

    /**
     * @Route("/internal/box-experience", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="box-experience")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BoxExperienceDeleteRequest::class)
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
    public function delete(BoxExperienceDeleteRequest $boxExperienceDeleteRequest, BoxExperienceManager $boxExperienceManager): Response
    {
        try {
            $boxExperienceManager->delete($boxExperienceDeleteRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}
