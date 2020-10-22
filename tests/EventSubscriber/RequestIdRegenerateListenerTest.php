<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\RequestIdRegenerateListener;
use App\Logger\Processor\RequestIdProcessor;
use App\Tests\ProphecyTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * @coversDefaultClass \App\EventSubscriber\RequestIdRegenerateListener
 */
class RequestIdRegenerateListenerTest extends ProphecyTestCase
{
    /**
     * @covers ::__construct
     * @covers ::regenerateRequestId
     */
    public function testRegenerateRequestId(): void
    {
        $requestProcessor = $this->prophesize(RequestIdProcessor::class);
        $listener = new RequestIdRegenerateListener($requestProcessor->reveal());

        $requestProcessor->regenerate()->shouldBeCalled();

        $listener->regenerateRequestId(new WorkerMessageReceivedEvent(new Envelope(new \stdClass()), ''));
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals([
            WorkerMessageReceivedEvent::class => ['regenerateRequestId', 100],
        ], RequestIdRegenerateListener::getSubscribedEvents());
    }
}
