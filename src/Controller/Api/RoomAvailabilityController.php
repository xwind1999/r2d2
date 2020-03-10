<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\RoomAvailability\RoomAvailabilityCreateRequest;
use App\Contract\Request\RoomAvailability\RoomAvailabilityUpdateRequest;
use App\Contract\Response\RoomAvailability\RoomAvailabilityCreateResponse;
use App\Contract\Response\RoomAvailability\RoomAvailabilityGetResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\RoomAvailabilityManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomAvailabilityController
{
    /**
     * @Route("/api/room-availability", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="room-availability")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RoomAvailabilityCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Room Availability created",
     *     @Model(type=RoomAvailabilityCreateResponse::class)
     * )
     */
    public function create(RoomAvailabilityCreateRequest $roomAvailabilityCreateRequest, RoomAvailabilityManager $roomAvailabilityManager): RoomAvailabilityCreateResponse
    {
        $roomAvailability = $roomAvailabilityManager->create($roomAvailabilityCreateRequest);

        return new RoomAvailabilityCreateResponse($roomAvailability);
    }

    /**
     * @Route("/api/room-availability/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="room-availability")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Room Availability successfully retrieved",
     *     @Model(type=RoomAvailabilityGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(string $uuid, RoomAvailabilityManager $roomAvailabilityManager): RoomAvailabilityGetResponse
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $roomAvailability = $roomAvailabilityManager->get($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new RoomAvailabilityGetResponse($roomAvailability);
    }

    /**
     * @Route("/api/room-availability/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="room-availability")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Room Availability deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(string $uuid, RoomAvailabilityManager $roomAvailabilityManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $roomAvailabilityManager->delete($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/room-availability/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="room-availability")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RoomAvailabilityUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Room Availability upated"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(string $uuid, RoomAvailabilityUpdateRequest $roomAvailabilityUpdateRequest, RoomAvailabilityManager $roomAvailabilityManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $roomAvailabilityManager->update($uuid, $roomAvailabilityUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}
