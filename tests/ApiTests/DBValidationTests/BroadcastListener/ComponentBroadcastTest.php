<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Helper\MoneyHelper;
use App\Repository\ComponentRepository;
use App\Tests\ApiTests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class ComponentBroadcastTest extends IntegrationTestCase
{
    public function testCreateComponentWithRoomTypeAsStock(): string
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

        $response = self::$broadcastListenerHelper->testComponentProduct($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        /** @var ComponentRepository $componentRepository */
        $componentRepository = self::$container->get(ComponentRepository::class);
        $component = $componentRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $component->goldenId);
        self::assertEquals($payload['partner']['id'], $component->partnerGoldenId);
        self::assertEquals($payload['isSellable'], $component->isSellable);
        self::assertEquals($payload['isReservable'], $component->isReservable);
        self::assertEquals($payload['roomStockType'], $component->roomStockType);
        self::assertEquals($payload['productDuration'], $component->duration);

        return $component->goldenId;
    }

    /**
     * @depends testCreateComponentWithRoomTypeAsStock
     */
    public function testUpdateComponentWithRoomTypeAsStockWithStatusInActive(string $componentID): void
    {
        static::cleanUp();

        $payload = [
            'id' => $componentID,
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
            'status' => 'inactive',
            'type' => 'component',
            'isSellable' => true,
            'isReservable' => false,
            'listPrice' => [
                'currencyCode' => 'EUR',
                'amount' => 100,
            ],
        ];

        $response = self::$broadcastListenerHelper->testComponentProduct($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $componentRepository = self::$container->get(ComponentRepository::class);
        $component = $componentRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $component->goldenId);
        self::assertEquals($payload['partner']['id'], $component->partnerGoldenId);
        self::assertEquals($payload['isSellable'], $component->isSellable);
        self::assertEquals($payload['isReservable'], $component->isReservable);
        self::assertEquals($payload['roomStockType'], $component->roomStockType);
        self::assertEquals($payload['productDuration'], $component->duration);
    }

    public function testCreateComponentWithRoomTypeAsOnRequest(): string
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

        $response = self::$broadcastListenerHelper->testComponentProduct($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $componentRepository = self::$container->get(ComponentRepository::class);
        $component = $componentRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $component->goldenId);
        self::assertEquals($payload['partner']['id'], $component->partnerGoldenId);
        self::assertEquals($payload['isSellable'], $component->isSellable);
        self::assertEquals($payload['isReservable'], $component->isReservable);
        self::assertEquals($payload['roomStockType'], $component->roomStockType);
        self::assertEquals($payload['productDuration'], $component->duration);

        return $component->goldenId;
    }

    /**
     * @depends testCreateComponentWithRoomTypeAsOnRequest
     */
    public function testUpdateComponentWithRoomTypeAsOnRequestWithStatusInActive(string $componentID): void
    {
        $payload = [
            'id' => $componentID,
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
            'status' => 'inactive',
            'type' => 'component',
            'isSellable' => true,
            'isReservable' => true,
            'listPrice' => [
                'currencyCode' => 'EUR',
                'amount' => 100,
            ],
        ];

        $response = self::$broadcastListenerHelper->testComponentProduct($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $componentRepository = self::$container->get(ComponentRepository::class);
        $component = $componentRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $component->goldenId);
        self::assertEquals($payload['partner']['id'], $component->partnerGoldenId);
        self::assertEquals($payload['isSellable'], $component->isSellable);
        self::assertEquals($payload['isReservable'], $component->isReservable);
        self::assertEquals($payload['roomStockType'], $component->roomStockType);
        self::assertEquals($payload['productDuration'], $component->duration);
    }

    public function testCreateComponentWithListComponentPrice(): void
    {
        static::cleanUp();

        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'Test component 5',
            'description' => 'Test component with component price with dot',
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
                'amount' => 100.20,
            ],
        ];

        $response = self::$broadcastListenerHelper->testComponentProduct($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $moneyHelper = new MoneyHelper();
        /** @var ComponentRepository $componentRepository */
        $componentRepository = self::$container->get(ComponentRepository::class);
        $component = $componentRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $component->goldenId);
        self::assertEquals($payload['partner']['id'], $component->partnerGoldenId);
        self::assertEquals($payload['isSellable'], $component->isSellable);
        self::assertEquals($payload['isReservable'], $component->isReservable);
        self::assertEquals($payload['roomStockType'], $component->roomStockType);
        self::assertEquals($payload['productDuration'], $component->duration);
        self::assertEquals($payload['listPrice']['currencyCode'], $component->currency);
        self::assertEquals($moneyHelper->convertToInteger('100.20', 'EUR'), $component->price);
    }
}
