<?php

declare(strict_types=1);

namespace App\Controller\BroadcastListener;

use App\Contract\Request\BroadcastListener\PartnerRequest;
use App\Contract\Request\BroadcastListener\ProductRequest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BroadcastListenerController
{
    /**
     * @Route("/broadcast-listener/product", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="products")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ProductRequest::class)
     * )
     * @SWG\Response(
     *     description="Product handled",
     *     @Model(type=Response::class)
     * )
     */
    public function productListener(ProductRequest $productRequest): Response
    {
        //TODO logic
        return new Response(null, 202);
    }

    /**
     * @Route("/broadcast-listener/partner", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="partners")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=PartnerRequest::class)
     * )
     * @SWG\Response(
     *     description="Partner handled",
     *     @Model(type=PartnerBroadcastResponse::class)
     * )
     */
    public function partnerListener(PartnerRequest $partnerRequest): Response
    {
        //TODO logic
        return new Response(null, 202);
    }
}
