<?php

declare(strict_types=1);

namespace App\Messenger\EventListener;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;

class EntityManagerClosedExceptionListener implements EventSubscriberInterface
{
    private const THE_ENTITY_MANAGER_IS_CLOSED = 'The EntityManager is closed.';

    private CacheItemPoolInterface $restartSignalCachePool;

    public function __construct(CacheItemPoolInterface $restartSignalCachePool)
    {
        $this->restartSignalCachePool = $restartSignalCachePool;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => ['onMessageFailed', 100],
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $throwable = $event->getThrowable();

        if (self::THE_ENTITY_MANAGER_IS_CLOSED === $throwable->getMessage()) {
            $cacheItem = $this
                ->restartSignalCachePool
                ->getItem(StopWorkerOnRestartSignalListener::RESTART_REQUESTED_TIMESTAMP_KEY);

            $cacheItem->set(microtime(true));
            $this->restartSignalCachePool->save($cacheItem);
        }
    }
}
