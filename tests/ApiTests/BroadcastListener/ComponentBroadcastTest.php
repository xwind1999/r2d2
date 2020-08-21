<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\BroadcastListener;

use App\Repository\ComponentRepository;
use App\Tests\ApiTests\IntegrationTestCase;

class ComponentBroadcastTest extends IntegrationTestCase
{
    public function testCreateBoxWithRoomTypeAsStock()
    {
        static::cleanUp();

        $payload = [
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

        $response = self::$broadcastListenerHelper->testProducts($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('broadcast-listeners-product');

        /** @var ComponentRepository $componentRepository */
        $componentRepository = self::$container->get(ComponentRepository::class);
        $component = $componentRepository->findOneByGoldenId($payload['id']);
        $this->assertEquals($payload['id'], $component->goldenId);
        $this->assertEquals($payload['partner']['id'], $component->partnerGoldenId);
        $this->assertEquals($payload['isSellable'], $component->isSellable);
        $this->assertEquals($payload['isReservable'], $component->isReservable);
        $this->assertEquals($payload['roomStockType'], $component->roomStockType);
        $this->assertEquals($payload['productDuration'], $component->duration);
    }

    public function testCreateBoxWithRoomTypeAsOnRequest()
    {
        static::cleanUp();

        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'Test component 2',
            'description' => 'Test component Ignore with Room Type as On Request',
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'partner' => [
                'id' => '123456',
            ],
            'roomStockType' => 'on_request',
            'productDuration' => 2,
            'status' => 'active',
            'type' => 'component',
            'isSellable' => true,
            'isReservable' => true,
            'listPrice' => [
                'currencyCode' => 'EUR',
                'amount' => 100,
            ],
        ];

        $response = self::$broadcastListenerHelper->testProducts($payload);
        $this->assertEquals(202, $response->getStatusCode());

        $this->consume('broadcast-listeners-product');

        /** @var ComponentRepository $componentRepository */
        $componentRepository = self::$container->get(ComponentRepository::class);
        $component = $componentRepository->findOneByGoldenId($payload['id']);
        $this->assertEquals($payload['id'], $component->goldenId);
        $this->assertEquals($payload['partner']['id'], $component->partnerGoldenId);
        $this->assertEquals($payload['isSellable'], $component->isSellable);
        $this->assertEquals($payload['isReservable'], $component->isReservable);
        $this->assertEquals($payload['roomStockType'], $component->roomStockType);
        $this->assertEquals($payload['productDuration'], $component->duration);
    }
}
