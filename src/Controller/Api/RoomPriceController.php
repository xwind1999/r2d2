<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\RoomPrice\RoomPriceCreateRequest;
use App\Contract\Request\RoomPrice\RoomPriceUpdateRequest;
use App\Contract\Response\RoomPrice\RoomPriceCreateResponse;
use App\Contract\Response\RoomPrice\RoomPriceGetResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RoomPriceManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomPriceController
{
    /**
     * @Route("/api/room-price", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="room-price")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RoomPriceCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Booking Date created",
     *     @Model(type=RoomPriceCreateResponse::class)
     * )l
     */
    public function create(RoomPriceCreateRequest $roomPriceCreateRequest, RoomPriceManager $roomPriceManager): RoomPriceCreateResponse
    {
        $roomPrice = $roomPriceManager->create($roomPriceCreateRequest);

        return new RoomPriceCreateResponse($roomPrice);
    }

    /**
     * @Route("/api/room-price/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="room-price")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking Date successfully retrieved",
     *     @Model(type=RoomPriceGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(string $uuid, RoomPriceManager $roomPriceManager): RoomPriceGetResponse
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $roomPrice = $roomPriceManager->get($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new RoomPriceGetResponse($roomPrice);
    }

    /**
     * @Route("/api/room-price/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="room-price")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking Date deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(string $uuid, RoomPriceManager $roomPriceManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $roomPriceManager->delete($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/room-price/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="room-price")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RoomPriceUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Booking Date upated"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(string $uuid, RoomPriceUpdateRequest $roomPriceUpdateRequest, RoomPriceManager $roomPriceManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $roomPriceManager->update($uuid, $roomPriceUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}
