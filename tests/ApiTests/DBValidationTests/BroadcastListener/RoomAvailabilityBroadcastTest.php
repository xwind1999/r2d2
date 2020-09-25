<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

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

        $response = self::$broadcastListenerHelper->testComponentProduct($component);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume(self::QUEUE_BROADCAST_PRODUCT);

        self::$container->get(ComponentRepository::class)->findOneByGoldenId($component['id']);

        $date = new \DateTime();
        $date1 = new \DateTime('+2 week');
        $date2 = new \DateTime('+2 week 2 days');
        $date3 = new \DateTime('+2 week 3 days');
        $date4 = new \DateTime('+2 week 4 days');
        $date5 = new \DateTime('+2 week 5 days');
        $date6 = new \DateTime('+3 weeks');
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
                'dateTo' => $date4->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => false,
                'updatedAt' => $date->format('Y-m-d\TH:i:s.uP'),
                'quantity' => '50',
            ],
            [
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date5->format('Y-m-d\TH:i:s.uP'),
                'dateTo' => $date6->format('Y-m-d\TH:i:s.uP'),
                'isStopSale' => true,
                'updatedAt' => $date->format('Y-m-d\TH:i:s.uP'),
                'quantity' => '50',
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-room-availability-list', 3);
        $this->consume('listener-room-availability', 3);

        /** @var RoomAvailabilityRepository $roomAvailabilityRepository */
        $componentRepository = self::$container->get(ComponentRepository::class);
        $component1 = $componentRepository->findOneByGoldenId($payload[0]['product']['id']);
        $roomAvailabilityRepository = self::$container->get(RoomAvailabilityRepository::class);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($component1, new \DateTime($payload[0]['dateFrom']), new \DateTime($payload[0]['dateTo']));
        $this->assertEquals($payload[0]['product']['id'], $roomAvailability[$date1->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[0]['quantity'], $roomAvailability[$date1->format('Y-m-d')]->stock);
        $this->assertEquals(false, $roomAvailability[$date1->format('Y-m-d')]->isStopSale);

        $component2 = $componentRepository->findOneByGoldenId($payload[1]['product']['id']);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($component2, new \DateTime($payload[1]['dateFrom']), new \DateTime($payload[1]['dateTo']));
        $this->assertEquals($payload[1]['product']['id'], $roomAvailability[$date3->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[1]['quantity'], $roomAvailability[$date3->format('Y-m-d')]->stock);
        $this->assertEquals($payload[1]['isStopSale'], $roomAvailability[$date3->format('Y-m-d')]->isStopSale);

        $component3 = $componentRepository->findOneByGoldenId($payload[2]['product']['id']);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($component3, new \DateTime($payload[2]['dateFrom']), new \DateTime($payload[2]['dateTo']));
        $this->assertEquals($payload[2]['product']['id'], $roomAvailability[$date5->format('Y-m-d')]->componentGoldenId);
        $this->assertEquals($payload[2]['quantity'], $roomAvailability[$date5->format('Y-m-d')]->stock);
        $this->assertEquals($payload[2]['isStopSale'], $roomAvailability[$date5->format('Y-m-d')]->isStopSale);
    }
}
