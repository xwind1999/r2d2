<?php

declare(strict_types=1);

namespace App\Tests\Helper\Serializer\EventSubscriber;

use App\Contract\Request\BroadcastListener\Common\Price;
use App\Helper\MoneyHelper;
use App\Helper\Serializer\EventSubscriber\MoneyEventSubscriber;
use App\Tests\ProphecyTestCase;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

class MoneyEventSubscriberTest extends ProphecyTestCase
{
    private $moneyHelper;

    private MoneyEventSubscriber $moneyEventSubscriber;

    public function setUp(): void
    {
        $this->moneyHelper = $this->prophesize(MoneyHelper::class);
        $this->moneyEventSubscriber = new MoneyEventSubscriber($this->moneyHelper->reveal());
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [[
                'event' => 'serializer.pre_deserialize',
                'method' => 'onPreDeserialize',
                'class' => Price::class,
            ]],
            MoneyEventSubscriber::getSubscribedEvents()
        );
    }

    public function testOnPreDeserialize()
    {
        $data = [
            'amount' => 30.00,
            'currencyCode' => 'EUR',
        ];

        $event = new PreDeserializeEvent($this->prophesize(DeserializationContext::class)->reveal(), $data, ['name' => Price::class]);
        $this->moneyHelper->convertToInteger((string) $data['amount'], $data['currencyCode'])->willReturn(3000)->shouldBeCalled();

        $this->moneyEventSubscriber->onPreDeserialize($event);

        $this->assertEquals(3000, $event->getData()['amount']);
    }
}
