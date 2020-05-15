<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\Booking\BookingCreateRequest;
use App\Manager\BookingManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController
{
    /**
     * @Route("/booking", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="booking")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=BookingCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Booking created"
     * )
     * @SWG\Response(
     *     response=422,
     *     description="Unprocessable entity"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Internal server error"
     * )
     * @SWG\Response(
     *     response=409,
     *     description="Booking already exists"
     * )
     * @SWG\Response(
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
}
