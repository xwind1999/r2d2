<?php

declare(strict_types=1);

namespace App\Controller\BroadcastListener;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use App\Contract\Request\BroadcastListener\RelationshipRequest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class BroadcastListenerController
{
    /**
     * @Route("/api/broadcast-listener/product", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ProductRequest::class)
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Product handled")
     * )
     */
    public function productListener(ProductRequest $productRequest, MessageBusInterface $messageBus): Response
    {
        $messageBus->dispatch($productRequest);

        return new Response(null, 202);
    }

    /**
     * @Route("/api/broadcast-listener/partner", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=PartnerRequest::class)
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Partner handled")
     * )
     */
    public function partnerListener(PartnerRequest $partnerRequest, MessageBusInterface $messageBus): Response
    {
        $messageBus->dispatch($partnerRequest);

        return new Response(null, 202);
    }

    /**
     * @Route("/api/broadcast-listener/product-relationship", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="broadcast-listener")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=RelationshipRequest::class)
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Relationship handled")
     * )
     */
    public function relationshipListener(RelationshipRequest $relationshipRequest, MessageBusInterface $messageBus): Response
    {
        $messageBus->dispatch($relationshipRequest);

        return new Response(null, 202);
    }
}
