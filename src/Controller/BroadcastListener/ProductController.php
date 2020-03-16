<?php

declare(strict_types=1);

namespace App\Controller\BroadcastListener;

use App\Contract\Request\Product\ProductCreateRequest;
use App\Contract\Request\Product\ProductUpdateRequest;
use App\Contract\Response\Product\ProductCreateResponse;
use App\Exception\Http\UnprocessableEntityException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController
{
    /**
     * @Route("/broadcast-listeners/products", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="products")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ProductsCreateRequest::class)
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Product created",
     *     @Model(type=ProductsCreateResponse::class)
     * )
     */
    public function create(ProductCreateRequest $productsCreateRequest): ProductCreateResponse
    {
        //TODO logic
        return new ProductCreateResponse($productsCreateRequest);
    }

    /**
     * @Route("/broadcast-listeners/products", methods={"PUT"}, format="json")
     *
     * @SWG\Tag(name="products")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=ProductUpdateRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Product updated"
     * )
     *
     * @throws UnprocessableEntityException
     */
    public function put(ProductUpdateRequest $productUpdateRequest): Response
    {
        // TODO logic
        return new Response(null, 202);
    }
}
