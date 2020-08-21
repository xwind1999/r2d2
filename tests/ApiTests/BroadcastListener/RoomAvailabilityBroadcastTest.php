<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\BroadcastListener;

use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class RoomAvailabilityBroadcastTest extends IntegrationTestCase
{
    public function testCreateRoomAvailability()
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

        self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);

        $date = new \DateTime();
        $date1 = new \DateTime('+2 week');
        $date2 = new \DateTime('+2 week 2 days');
        $date3 = new \DateTime('+2 week 3 days');
        $date4 = new \DateTime('+2 week 4 days');
        $payload = [[
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date1->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => $date2->format('Y-m-d\TH:i:s.uP'),
                'updatedAt' => $date->format('Y-m-d\TH:i:s.uP'),
                'quantity' => '50',
            ],
            [
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date3->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => $date3->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'updatedAt' => $date->format('Y-m-d\TH:i:s.uP'),
                'quantity' => '50',
            ],
            [
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date4->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => $date4->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => true,
                'updatedAt' => $date->format('Y-m-d\TH:i:s.uP'),
                'quantity' => '50',
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('broadcast-listeners-room-availability', 3);

        /** @var RoomAvailabilityRepository $roomAvailabilityRepository */
        $roomAvailabilityRepository = self::$container->get(RoomAvailabilityRepository::class);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($payload[0]['product']['id'], new \DateTime($payload[0]['dateFrom']), new \DateTime($payload[0]['dateTo']));
        $this->assertEquals($payload[0]['product']['id'], $roomAvailability[$date1->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[0]['quantity'], $roomAvailability[$date1->format('Y-m-d')]->stock);
        $this->assertEquals(false, $roomAvailability[$date1->format('Y-m-d')]->isStopSale);
        $this->assertEquals($payload[0]['product']['id'], $roomAvailability[$date2->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[0]['quantity'], $roomAvailability[$date2->format('Y-m-d')]->stock);
        $this->assertEquals(false, $roomAvailability[$date1->format('Y-m-d')]->isStopSale);

        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($payload[1]['product']['id'], new \DateTime($payload[1]['dateFrom']), new \DateTime($payload[1]['dateTo']));
        $this->assertEquals($payload[1]['product']['id'], $roomAvailability[$date3->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[1]['quantity'], $roomAvailability[$date3->format('Y-m-d')]->stock);
        $this->assertEquals($payload[1]['isStopSale'], $roomAvailability[$date3->format('Y-m-d')]->isStopSale);

        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($payload[2]['product']['id'], new \DateTime($payload[2]['dateFrom']), new \DateTime($payload[2]['dateTo']));
        $this->assertEquals($payload[2]['product']['id'], $roomAvailability[$date4->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[2]['quantity'], $roomAvailability[$date4->format('Y-m-d')]->stock);
        $this->assertEquals($payload[2]['isStopSale'], $roomAvailability[$date4->format('Y-m-d')]->isStopSale);
    }
}
