<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\BroadcastListener;

use App\Repository\ComponentRepository;
use App\Repository\RoomPriceRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class RoomPriceBroadcastTest extends IntegrationTestCase
{
    public function testCreateRoomPrice()
    {
        static::cleanUp();

        $component = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'Test component 1',
            'description' => 'Test component Ignore with Room Type as Stock',
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'partner' => [
                'id' => '12345678',
            ],
            'roomStockType' => 'stock',
            'productDuration' => 2,
            'status' => 'active',
            'type' => 'component',
            'isSellable' => true,
            'isReservable' => false,
            'listPrice' => [
                'currencyCode' => 'EUR',
                'amount' => 100,
            ],
        ];

        $response = self::$broadcastListenerHelper->testProducts($component);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        $componentEntity = self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);

        $date = new \DateTime();
        $date1 = new \DateTime('+2 week');
        $date2 = new \DateTime('+2 week 2 days');
        $payload = [[
            'product' => [
                'id' => $component['id'],
            ],
            'dateFrom' => $date1->format('Y-m-d\TH:i:s.uP'),
            'dateTo' => $date2->format('Y-m-d\TH:i:s.uP'),
            'updatedAt' => $date->format('Y-m-d\TH:i:s.uP'),
            'price' => [
                'currencyCode' => 'EUR',
                'amount' => 180,
            ],
        ]];

        $response = self::$broadcastListenerHelper->testRoomPrice($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('broadcast-listeners-room-price');

        /** @var RoomPriceRepository $roomPriceRepository */
        $roomPriceRepository = self::$container->get(RoomPriceRepository::class);
        $roomPrice = $roomPriceRepository->findByComponentAndDateRange($componentEntity, new \DateTime($payload[0]['dateFrom']), new \DateTime($payload[0]['dateTo']));
        $this->assertEquals($payload[0]['product']['id'], $roomPrice[$date2->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[0]['price']['amount'], ($roomPrice[$date2->format('Y-m-d')]->price) / 100);
        $this->assertEquals($payload[0]['product']['id'], $roomPrice[$date1->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[0]['price']['amount'], ($roomPrice[$date1->format('Y-m-d')]->price) / 100);
    }
}
