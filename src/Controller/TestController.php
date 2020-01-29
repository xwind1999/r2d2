<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\Test\PostEchoRequest;
use App\Contract\Response\Test\PostEchoResponse;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @Route("/api/tests/echo", methods={"POST"}, format="json")
     *
     * @SWG\Tag(name="test")
     * @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         @Model(type=PostEchoRequest::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Echoes the message",
     *     @Model(type=PostEchoResponse::class)
     * )
     */
    public function echo(PostEchoRequest $echo, SerializerInterface $serializer): PostEchoResponse
    {
        $response = new PostEchoResponse();
        $response->setMessage($echo->message);

        return $response;
    }
}
