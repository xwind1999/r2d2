<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Contract\Response\Booking\BookingCreateResponse;
use App\Contract\Response\Booking\BookingGetResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\BookingManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController
{
    /**
     * @Route("/api/booking", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BookingCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Booking  created",
     *     @Model(type=BookingCreateResponse::class)
     * )l
     */
    public function create(BookingCreateRequest $bookingCreateRequest, BookingManager $bookingManager): BookingCreateResponse
    {
        $booking = $bookingManager->create($bookingCreateRequest);

        return new BookingCreateResponse($booking);
    }

    /**
     * @Route("/api/booking/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking  successfully retrieved",
     *     @Model(type=BookingGetResponse::class)
     * )
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(string $uuid, BookingManager $bookingManager): BookingGetResponse
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $booking = $bookingManager->get($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new BookingGetResponse($booking);
    }

    /**
     * @Route("/api/booking/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Booking  deleted"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(string $uuid, BookingManager $bookingManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $bookingManager->delete($uuid);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/api/booking/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BookingUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Booking  upated"
     * )
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(string $uuid, BookingUpdateRequest $bookingUpdateRequest, BookingManager $bookingManager): Response
    {
        if (!Uuid::isValid($uuid)) {
            throw new UnprocessableEntityException();
        }

        try {
            $bookingManager->update($uuid, $bookingUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }
}