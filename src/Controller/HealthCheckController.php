<?php

declare(strict_types=1);

namespace App\Controller;

use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController
{
    /**
     * @Route("/ping", methods={"GET"}, format="json")
     * @OA\Tag(name="healthcheck")
     * @OA\Response(
     *     description="Pong",
     *     response="204"
     * )
     */
    public function ping(): Response
    {
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
