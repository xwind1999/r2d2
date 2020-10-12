<?php

declare(strict_types=1);

namespace App\Controller\Internal;

use App\Contract\Request\Internal\Box\BoxCreateRequest;
use App\Contract\Request\Internal\Box\BoxUpdateRequest;
use App\Contract\Response\Internal\Box\BoxCreateResponse;
use App\Contract\Response\Internal\Box\BoxGetResponse;
use App\Contract\Response\Internal\Box\BoxUpdateResponse;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BoxManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BoxController
{
    /**
     * @Route("/internal/box", methods={"POST"}, format="json")
     *
     * @OA\Tag(name="box")
     * @OA\Parameter(
     *         name="body",
     *         in="query",
     *         @Model(type=BoxCreateRequest::class)
     * )
     * @OA\Response(
     *     response=201,
     *     description="Box created",
     *     @Model(type=BoxCreateResponse::class)
     * )
     * @Security(name="basic")
     */
    public function create(BoxCreateRequest $boxCreateRequest, BoxManager $boxManager): BoxCreateResponse
    {
        try {
            $box = $boxManager->create($boxCreateRequest);
        } catch (UniqueConstraintViolationException $exception) {
            throw ResourceConflictException::forContext([], $exception);
        }

        return new BoxCreateResponse($box);
    }

    /**
     * @Route("/internal/box/{uuid}", methods={"GET"}, format="json")
     *
     * @OA\Tag(name="box")
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
     *     description="Box successfully retrieved",
     *     @Model(type=BoxGetResponse::class)
     * )
     * @Security(name="basic")
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(UuidInterface $uuid, BoxManager $boxManager): BoxGetResponse
    {
        try {
            $box = $boxManager->get($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new BoxGetResponse($box);
    }

    /**
     * @Route("/internal/box/{uuid}", methods={"DELETE"}, format="json")
     *
     * @OA\Tag(name="box")
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
     *     description="Box deleted"
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(UuidInterface $uuid, BoxManager $boxManager): Response
    {
        try {
            $boxManager->delete($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/internal/box/{uuid}", methods={"PUT"}, format="json")
     *
     * @OA\Tag(name="box")
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
     *         @Model(type=BoxUpdateRequest::class)
     * )
     * @OA\Response(
     *     response=200,
     *     description="Box successfully updated",
     *     @Model(type=BoxUpdateResponse::class)
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(UuidInterface $uuid, BoxUpdateRequest $boxUpdateRequest, BoxManager $boxManager): BoxUpdateResponse
    {
        try {
            $box = $boxManager->update($uuid->toString(), $boxUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new BoxUpdateResponse($box);
    }
}
