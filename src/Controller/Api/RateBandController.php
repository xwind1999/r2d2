<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\RateBand\RateBandCreateRequest;
use App\Contract\Request\RateBand\RateBandUpdateRequest;
use App\Contract\Response\RateBand\RateBandCreateResponse;
use App\Contract\Response\RateBand\RateBandGetResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RateBandManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RateBandController
{
    /**
     * @Route("/api/rate-band", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="rate-band")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RateBandCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="RateBand created",
     *     @Model(type=RateBandCreateResponse::class)
     * )
     */
    public function create(RateBandCreateRequest $rateBandCreateRequest, RateBandManager $rateBandManager): RateBandCreateResponse
    {
        $rateBand = $rateBandManager->create($rateBandCreateRequest);

        return new RateBandCreateResponse($rateBand);
    }

    /**
     * @Route("/api/rate-band/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="rate-band")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="RateBand successfully retrieved",
     *     @Model(type=RateBandGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(string $uuid, RateBandManager $rateBandManager): RateBandGetResponse
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $rateBand = $rateBandManager->get($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new RateBandGetResponse($rateBand);
    }

    /**
     * @Route("/api/rate-band/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="rate-band")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="RateBand deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(string $uuid, RateBandManager $rateBandManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $rateBandManager->delete($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/rate-band/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="rate-band")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RateBandUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="RateBand updated"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(string $uuid, RateBandUpdateRequest $rateBandUpdateRequest, RateBandManager $rateBandManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $rateBandManager->update($uuid, $rateBandUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}
