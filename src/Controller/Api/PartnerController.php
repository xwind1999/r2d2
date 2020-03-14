<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Contract\Request\Partner\PartnerCreateRequest;
use App\Contract\Request\Partner\PartnerUpdateRequest;
use App\Contract\Response\Partner\PartnerCreateResponse;
use App\Contract\Response\Partner\PartnerGetResponse;
use App\Contract\Response\Partner\PartnerUpdateResponse;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Repository\EntityNotFoundException;
use App\Manager\PartnerManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PartnerController
{
    /**
     * @Route("/api/partner", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="partner")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=PartnerCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Partner created",
     *     @Model(type=PartnerCreateResponse::class)
     * )
     */
    public function create(PartnerCreateRequest $partnerCreateRequest, PartnerManager $partnerManager): PartnerCreateResponse
    {
        $partner = $partnerManager->create($partnerCreateRequest);

        return new PartnerCreateResponse($partner);
    }

    /**
     * @Route("/api/partner/{uuid}", methods={"GET"}, format="json")
     *
     * @SWG\Tag(name="partner")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Partner successfully retrieved",
     *     @Model(type=PartnerGetResponse::class)
     * )
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
     * @Route("/api/partner/{uuid}", methods={"DELETE"}, format="json")
     *
     * @SWG\Tag(name="partner")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Partner deleted"
     * )
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
     * @Route("/api/partner/{uuid}", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="partner")
     * @SWG\Parameter(
     *     name="uuid",
     *     in="path",
     *     type="string",
     *     format="uuid"
     * )
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=PartnerUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Partner updated",
     *     @Model(type=PartnerUpdateResponse::class)
     * )
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
