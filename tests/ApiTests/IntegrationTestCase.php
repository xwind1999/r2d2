<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Tests\ApiTests\Helper\DisposableWorker;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class IntegrationTestCase extends ApiTestCase
{
    protected const QUEUE_BROADCAST_PRODUCT = 'broadcast-listeners-product';
    protected const QUEUE_BROADCAST_RELATIONSHIP = 'broadcast-listeners-product-relationship';
    protected const QUEUE_CALCULATE_MANAGEABLE_FLAG = 'calculate-manageable-flag';
    protected const QUEUE_BROADCAST_PARTNER = 'broadcast-listeners-partner';

    public static function setUpBeforeClass(): void
    {
        if (self::$baseUrl) {
            self::markTestSkipped('This test cannot be run against an environment');
        }
        parent::setUpBeforeClass();

        // need to clear all the messages before starting these tests
        self::$container
            ->get('doctrine.orm.entity_manager')
            ->getConnection()
            ->exec('TRUNCATE messenger_messages')
        ;
    }

    protected function consume(string $queue, int $iterations = 1): void
    {
        /**
         * TODO: find a way to test in a real environment (devint),
         *       maybe by waiting a second for the workers to pick up the message.
         */
        $transport = self::$container->get('messenger.transport.'.$queue);
        (new DisposableWorker(
            [$transport],
            self::$container->get(MessageBusInterface::class),
            self::$container->get(EventDispatcherInterface::class)
        ))->run($iterations);
    }
}
