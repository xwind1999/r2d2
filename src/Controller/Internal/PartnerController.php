<?php

declare(strict_types=1);

namespace App\Controller\Internal;

use App\Contract\Request\Internal\Partner\PartnerCreateRequest;
use App\Contract\Request\Internal\Partner\PartnerUpdateRequest;
use App\Contract\Response\Internal\Partner\PartnerCreateResponse;
use App\Contract\Response\Internal\Partner\PartnerGetResponse;
use App\Contract\Response\Internal\Partner\PartnerUpdateResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\PartnerManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PartnerController
{
    /**
     * @Route("/internal/partner", methods={"POST"}, format="json")
     *
     * @OA\Tag(name="partner")
     * @OA\Parameter(
     *         name="body",
     *         in="query",
     *         @Model(type=PartnerCreateRequest::class)
     * )
     * @OA\Response(
     *     response=201,
     *     description="Partner created",
     *     @Model(type=PartnerCreateResponse::class)
     * )
     * @Security(name="basic")
     */
    public function create(PartnerCreateRequest $partnerCreateRequest, PartnerManager $partnerManager): PartnerCreateResponse
    {
        $partner = $partnerManager->create($partnerCreateRequest);

        return new PartnerCreateResponse($partner);
    }

    /**
     * @Route("/internal/partner/{uuid}", methods={"GET"}, format="json")
     *
     * @OA\Tag(name="partner")
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
     *     description="Partner successfully retrieved",
     *     @Model(type=PartnerGetResponse::class)
     * )
     * @Security(name="basic")
     *
     * @throws UnprocessableEntityException
     * @throws ResourceNotFoundException
     */
    public function get(UuidInterface $uuid, PartnerManager $partnerManager): PartnerGetResponse
    {
        try {
            $partner = $partnerManager->get($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new PartnerGetResponse($partner);
    }

    /**
     * @Route("/internal/partner/{uuid}", methods={"DELETE"}, format="json")
     *
     * @OA\Tag(name="partner")
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
     *     description="Partner deleted"
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function delete(UuidInterface $uuid, PartnerManager $partnerManager): Response
    {
        try {
            $partnerManager->delete($uuid->toString());
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/internal/partner/{uuid}", methods={"PUT"}, format="json")
     *
     * @OA\Tag(name="partner")
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
     *         @Model(type=PartnerUpdateRequest::class)
     * )
     * @OA\Response(
     *     response=200,
     *     description="Partner updated",
     *     @Model(type=PartnerUpdateResponse::class)
     * )
     * @Security(name="basic")
     *
     * @throws ResourceNotFoundException
     * @throws UnprocessableEntityException
     */
    public function put(UuidInterface $uuid, PartnerUpdateRequest $partnerUpdateRequest, PartnerManager $partnerManager): PartnerUpdateResponse
    {
        try {
            $partner = $partnerManager->update($uuid->toString(), $partnerUpdateRequest);
        } catch (EntityNotFoundException $exception) {
            throw new ResourceNotFoundException();
        }

        return new PartnerUpdateResponse($partner);
    }
}
