<?php

declare(strict_types=1);

namespace App\Tests\Messenger\EventListener;

use App\Messenger\EventListener\EntityManagerClosedExceptionListener;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;

/**
 * @coversDefaultClass \App\Messenger\EventListener\EntityManagerClosedExceptionListener
 */
class EntityManagerClosedExceptionListenerTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface
     */
    private $restartSignalCachePool;

    private EntityManagerClosedExceptionListener $eventListener;

    public function setUp(): void
    {
        $this->restartSignalCachePool = $this->prophesize(CacheItemPoolInterface::class);
        $this->eventListener = new EntityManagerClosedExceptionListener($this->restartSignalCachePool->reveal());
    }

    public function testOnMessageFailedNothingHappens()
    {
        $exc = new ORMException();
        $event = new WorkerMessageFailedEvent(new Envelope(new \stdClass()), '', $exc);

        $this->assertEmpty($this->eventListener->onMessageFailed($event));
    }

    public function testOnMessageFailedWillRestartWorkers()
    {
        $exc = ORMException::entityManagerClosed();
        $event = new WorkerMessageFailedEvent(new Envelope(new \stdClass()), '', $exc);

        $cacheItem = $this->prophesize(CacheItemInterface::class);

        $this
            ->restartSignalCachePool
            ->getItem(StopWorkerOnRestartSignalListener::RESTART_REQUESTED_TIMESTAMP_KEY)
            ->willReturn($cacheItem->reveal())
            ->shouldBeCalled();

        $cacheItem->set(Argument::type('float'))->shouldBeCalled();

        $this->restartSignalCachePool->save($cacheItem->reveal())->shouldBeCalled();

        $this->assertEmpty($this->eventListener->onMessageFailed($event));
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            WorkerMessageFailedEvent::class => ['onMessageFailed', 100],
        ], EntityManagerClosedExceptionListener::getSubscribedEvents());
    }
}
