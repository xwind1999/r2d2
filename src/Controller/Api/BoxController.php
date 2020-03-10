<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Box\BoxCreateRequest;
use App\Contract\Request\Box\BoxUpdateRequest;
use App\Contract\Response\Box\BoxCreateResponse;
use App\Contract\Response\Box\BoxGetResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BoxManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BoxController
{
    /**
     * @Route("/api/box", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="box")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BoxCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Box created",
     *     @Model(type=BoxCreateResponse::class)
     * )
     */
    public function create(BoxCreateRequest $boxCreateRequest, BoxManager $boxManager): BoxCreateResponse
    {
        $box = $boxManager->create($boxCreateRequest);

        return new BoxCreateResponse($box);
    }

    /**
     * @Route("/api/box/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="box")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Box successfully retrieved",
     *     @Model(type=BoxGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(string $uuid, BoxManager $boxManager): BoxGetResponse
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $box = $boxManager->get($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new BoxGetResponse($box);
    }

    /**
     * @Route("/api/box/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="box")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Box deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(string $uuid, BoxManager $boxManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $boxManager->delete($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/box/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="box")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BoxUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Box upated"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(string $uuid, BoxUpdateRequest $boxUpdateRequest, BoxManager $boxManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $boxManager->update($uuid, $boxUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}
