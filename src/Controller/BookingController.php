<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Contract\Request\Booking\BookingUpdateRequest;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Repository\BookingNotFoundException;
use App\Manager\BookingManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController
{
    /**
     * @Route("/booking", methods={"POST"}, format="json")
     *
     * @OA\Tag(name="booking")
     * @OA\RequestBody(
     *     @Model(type=BookingCreateRequest::class)
     * )
     * @OA\Response(
     *     response=201,
     *     description="Booking created"
     * )
     * @OA\Response(
     *     response=422,
     *     description="Unprocessable entity"
     * )
     * @OA\Response(
     *     response=500,
     *     description="Internal server error"
     * )
     * @OA\Response(
     *     response=409,
     *     description="Booking already exists"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     *
     * @Security(name="basic")
     */
    public function create(BookingCreateRequest $bookingCreateRequest, BookingManager $bookingManager): Response
    {
        $bookingManager->create($bookingCreateRequest);

        return new Response(null, Response::HTTP_CREATED);
    }

    /**
     * @Route("/booking", methods={"PATCH"}, format="json")
     *
     * @OA\Tag(name="booking")
     * @OA\RequestBody(
     *     @Model(type=BookingUpdateRequest::class)
     * )
     * @OA\Response(
     *     response=204,
     *     description="Booking updated"
     * )
     * @OA\Response(
     *     response=422,
     *     description="Unprocessable entity"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Booking not found"
     * )
     * @OA\Response(
     *     response=409,
     *     description="Booking already exists"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request"
     * )
     *
     * @Security(name="basic")
     */
    public function update(BookingUpdateRequest $bookingUpdateRequest, BookingManager $bookingManager): Response
    {
        try {
            $bookingManager->update($bookingUpdateRequest);
        } catch (BookingNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
