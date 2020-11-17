<?php

declare(strict_types=1);

namespace App\Helper\Serializer\EventSubscriber;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Helper\MoneyHelper;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

class MoneyEventSubscriber implements EventSubscriberInterface
{
    private MoneyHelper $moneyHelper;

    public function __construct(MoneyHelper $moneyHelper)
    {
        $this->moneyHelper = $moneyHelper;
    }

    public static function getSubscribedEvents()
    {
        return [[
            'event' => 'serializer.pre_deserialize',
            'method' => 'onPreDeserialize',
            'class' => Price::class,
        ]];
    }

    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        $amount = $this->moneyHelper->convertToInteger((string) $event->getData()['amount'], $event->getData()['currencyCode']);
        $event->setData([
            'amount' => $amount,
            'currencyCode' => $event->getData()['currencyCode'],
        ]);
    }
}
