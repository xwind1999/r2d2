<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Contract\ResponseContract;
use App\EventSubscriber\ActionToJsonResponse;
use App\Kernel;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ActionToJsonResponseTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|SerializerInterface
     */
    protected $serializer;

    public function setUp(): void
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals([KernelEvents::VIEW => 'returnedObjectToJsonResponse'], ActionToJsonResponse::getSubscribedEvents());
    }

    public function testReturnedObjectToJsonResponse()
    {
        $testResponse = new ResponseContract();
        $event = new ViewEvent(
            new Kernel('prod', false),
            new Request(),
            1,
            $testResponse
        );
        $actionToJsonResponse = new ActionToJsonResponse($this->serializer->reveal());
        $response = '{"message": "test2"}';
        $expectedResponse = new JsonResponse($response, JsonResponse::HTTP_OK, [], true);

        $this->serializer->serialize($testResponse, 'json', null)->willReturn($response)->shouldBeCalled();

        $actionToJsonResponse->returnedObjectToJsonResponse($event);

        $this->assertEquals($expectedResponse, $event->getResponse());
    }

    public function testReturnedObjectWontBeProcessed()
    {
        $testResponse = '';
        $event = new ViewEvent(
            new Kernel('prod', false),
            new Request(),
            1,
            $testResponse
        );
        $actionToJsonResponse = new ActionToJsonResponse($this->serializer->reveal());

        $actionToJsonResponse->returnedObjectToJsonResponse($event);

        $this->assertEquals($testResponse, $event->getResponse());
    }
}
