<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

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
        $event = new ViewEvent(
            new Kernel('test', false),
            new Request(),
            1,
            'test1'
        );
        $actionToJsonResponse = new ActionToJsonResponse($this->serializer->reveal());
        $expectedResponse = new JsonResponse('test2', JsonResponse::HTTP_OK, [], true);

        $this->serializer->serialize('test1', 'json', null)->willReturn('test2')->shouldBeCalled();

        $actionToJsonResponse->returnedObjectToJsonResponse($event);

        $this->assertEquals($expectedResponse, $event->getResponse());
    }
}
