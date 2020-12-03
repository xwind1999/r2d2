<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Constants\DateTimeConstants;
use App\Repository\ComponentRepository;
use App\Repository\RoomAvailabilityRepository;
use App\Tests\ApiTests\IntegrationTestCase;

/**
 * @group room_availability
 */
class RoomAvailabilityBroadcastTest extends IntegrationTestCase
{
    public function testCreateRoomAvailability(): void
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
        $date6 = new \DateTime('+2 week 6 days');
        $date7 = new \DateTime('+3 week');
        $date8 = new \DateTime('+3 weeks 1 day');
        $date9 = new \DateTime('+3 weeks 2 days');
        $date10 = new \DateTime('+3 weeks 3 days');
        $payload = [
            [
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date1->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => $date2->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'updatedAt' => $date->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'quantity' => '50',
            ],
            [
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date3->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => $date4->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => false,
                'updatedAt' => $date->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'quantity' => '50',
            ],
            [
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date5->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => $date6->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => true,
                'updatedAt' => $date->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'quantity' => '50',
            ],
            [
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date7->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => $date8->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => true,
                'updatedAt' => $date->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'quantity' => 0,
            ],
            [
                'product' => [
                    'id' => $component['id'],
                ],
                'dateFrom' => $date9->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'dateTo' => $date10->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'isStopSale' => false,
                'updatedAt' => $date->format(DateTimeConstants::DATE_TIME_MILLISECONDS),
                'quantity' => 0,
            ],
        ];

        $response = self::$broadcastListenerHelper->testRoomAvailability($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('listener-room-availability-list', count($payload));
        $this->consume('listener-room-availability', count($payload));

        $componentRepository = self::$container->get(ComponentRepository::class);
        $component1 = $componentRepository->findOneByGoldenId($payload[0]['product']['id']);
        $roomAvailabilityRepository = self::$container->get(RoomAvailabilityRepository::class);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($component1, new \DateTime($payload[0]['dateFrom']), new \DateTime($payload[0]['dateTo']));
        $this->assertEquals($payload[0]['product']['id'], $roomAvailability[$date1->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->componentGoldenId);
        $this->assertEquals($payload[0]['quantity'], $roomAvailability[$date1->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->stock);
        $this->assertEquals(false, $roomAvailability[$date1->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->isStopSale);

        $component2 = $componentRepository->findOneByGoldenId($payload[1]['product']['id']);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($component2, new \DateTime($payload[1]['dateFrom']), new \DateTime($payload[1]['dateTo']));
        $this->assertEquals($payload[1]['product']['id'], $roomAvailability[$date3->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->componentGoldenId);
        $this->assertEquals($payload[1]['quantity'], $roomAvailability[$date3->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->stock);
        $this->assertEquals($payload[1]['isStopSale'], $roomAvailability[$date3->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->isStopSale);

        $component3 = $componentRepository->findOneByGoldenId($payload[2]['product']['id']);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange($component3, new \DateTime($payload[2]['dateFrom']), new \DateTime($payload[2]['dateTo']));
        $this->assertEquals($payload[2]['product']['id'], $roomAvailability[$date5->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->componentGoldenId);
        $this->assertEquals($payload[2]['quantity'], $roomAvailability[$date5->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->stock);
        $this->assertEquals($payload[2]['isStopSale'], $roomAvailability[$date5->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->isStopSale);

        $component4 = $componentRepository->findOneByGoldenId($payload[3]['product']['id']);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange(
            $component4,
            new \DateTime($payload[3]['dateFrom']),
            new \DateTime($payload[3]['dateTo'])
        );
        $this->assertEquals($payload[3]['product']['id'], $roomAvailability[$date7->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->componentGoldenId);
        $this->assertEquals($payload[3]['quantity'], $roomAvailability[$date7->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->stock);
        $this->assertEquals($payload[3]['isStopSale'], $roomAvailability[$date7->format(DateTimeConstants::DEFAULT_DATE_FORMAT)]->isStopSale);

        $component5 = $componentRepository->findOneByGoldenId($payload[4]['product']['id']);
        $roomAvailability = $roomAvailabilityRepository->findByComponentAndDateRange(
            $component5,
            new \DateTime($payload[4]['dateFrom']),
            new \DateTime($payload[4]['dateTo'])
        );
        $this->assertEmpty($roomAvailability);
    }
}
