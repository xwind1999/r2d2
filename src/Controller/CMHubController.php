<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\CMHub\GetAvailabilityRequest;
use App\Contract\Response\CMHub\CMHubResponse;
use App\Provider\AvailabilityProvider;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

class CMHubController
{
    /**
     * @Route("/api/availability/", methods={"GET"}, format="json")
     *
     * @SWG\Parameter(
     *     name="productId",
     *     in="query",
     *     type="integer",
     *     format="integer",
     *     description="Product ID (example: 286201)"
     * )
     * @SWG\Parameter(
     *     name="start",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Parameter(
     *     name="end",
     *     in="query",
     *     type="string",
     *     format="date"
     * )
     * @SWG\Tag(name="cmhub")
     * @SWG\Response(
     *     description="CMHub handled",
     *     response="200"
     * )
     */
    public function getAvailability(
        GetAvailabilityRequest $getAvailability,
        AvailabilityProvider $legacyAvailabilityProvider
    ): CMHubResponse {
        return $legacyAvailabilityProvider->getAvailability(
            $getAvailability->productId,
            $getAvailability->start,
            $getAvailability->end
        );
    }
}
