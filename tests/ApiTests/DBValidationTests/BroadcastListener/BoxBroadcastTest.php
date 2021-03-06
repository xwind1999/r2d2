<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\DBValidationTests;

use App\Repository\BoxRepository;
use App\Tests\ApiTests\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class BoxBroadcastTest extends IntegrationTestCase
{
    public function testCreateBox(): string
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'product name',
            'description' => 'product description',
            'universe' => [
                'id' => 'STA',
            ],
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'sellableCountry' => [
                'code' => 'FR',
            ],
            'status' => 'live',
            'type' => 'mev',
        ];

        $response = self::$broadcastListenerHelper->testBoxProduct($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $boxRepository = self::$container->get(BoxRepository::class);
        $box = $boxRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $box->goldenId);
        self::assertEquals($payload['status'], $box->status);
        self::assertEquals($payload['sellableBrand']['code'], $box->brand);
        self::assertEquals($payload['sellableCountry']['code'], $box->country);

        return $box->goldenId;
    }

    /**
     * @depends testCreateBox
     */
    public function testUpdateExistingBox(string $boxID): void
    {
        $payload = [
            'id' => $boxID,
            'name' => 'product name',
            'description' => 'product description',
            'universe' => [
                'id' => 'STA',
            ],
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'sellableCountry' => [
                'code' => 'ES',
            ],
            'status' => 'live',
            'type' => 'mev',
        ];

        $response = self::$broadcastListenerHelper->testBoxProduct($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $boxRepository = self::$container->get(BoxRepository::class);
        $box = $boxRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $box->goldenId);
        self::assertEquals($payload['status'], $box->status);
        self::assertEquals($payload['sellableBrand']['code'], $box->brand);
        self::assertEquals($payload['sellableCountry']['code'], $box->country);
    }

    /**
     * @depends testCreateBox
     */
    public function testUpdateExistingBoxWithStatusInActive(string $boxID): void
    {
        $payload = [
            'id' => $boxID,
            'name' => 'product name',
            'description' => 'product description',
            'universe' => [
                'id' => 'STA',
            ],
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'sellableCountry' => [
                'code' => 'ES',
            ],
            'status' => 'inactive',
            'type' => 'mev',
        ];

        $response = self::$broadcastListenerHelper->testBoxProduct($payload);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->consume('listener-product');

        $boxRepository = self::$container->get(BoxRepository::class);
        $box = $boxRepository->findOneByGoldenId($payload['id']);
        self::assertEquals($payload['id'], $box->goldenId);
        self::assertEquals($payload['status'], $box->status);
        self::assertEquals($payload['sellableBrand']['code'], $box->brand);
        self::assertEquals($payload['sellableCountry']['code'], $box->country);
    }
}
