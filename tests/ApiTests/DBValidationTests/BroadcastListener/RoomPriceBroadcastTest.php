<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Repository\ComponentRepository;
use App\Repository\RoomPriceRepository;
use App\Tests\ApiTests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class RoomPriceBroadcastTest extends IntegrationTestCase
{
    public function testCreateRoomPrice(): void
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

        $response = self::$broadcastListenerHelper->testComponentProduct($component);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

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
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-room-price-list');
        $this->consume('listener-room-price');

        $roomPriceRepository = self::$container->get(RoomPriceRepository::class);
        $roomPrice = $roomPriceRepository->findByComponentAndDateRange($componentEntity, new \DateTime($payload[0]['dateFrom']), new \DateTime($payload[0]['dateTo']));
        self::assertEquals($payload[0]['product']['id'], $roomPrice[$date1->format('Y-m-d')]->componentGoldenId);
        self::assertEquals($payload[0]['price']['amount'], ($roomPrice[$date1->format('Y-m-d')]->price) / 100);
    }
}
