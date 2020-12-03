<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class BroadcastListenerHelper
{
    private const API_PRODUCT_BASE_URL = '/broadcast-listener/product';
    private const API_PARTNER_BASE_URL = '/broadcast-listener/partner';
    private const API_RELATIONSHIP_BASE_URL = '/broadcast-listener/product-relationship';
    private const API_PRICE_INFORMATION_BASE_URL = '/broadcast-listener/price-information';
    private const API_ROOM_AVAILABILITY_BASE_URL = '/broadcast-listener/room-availability';
    private const API_ROOM_PRICE_BASE_URL = '/broadcast-listener/room-price';

    protected AbstractBrowser $client;
    protected Serializer $serializer;
    protected ?string $baseUrl = null;

    public function __construct(AbstractBrowser $client, Serializer $serializer, ?string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
    }

    public function getDefaultBoxProduct(array $overrides = []): array
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'product name',
            'description' => 'product description',
            'universe' => [
                'id' => 'STA',
            ],
            'isSellable' => true,
            'isReservable' => true,
            'partner' => [
                'id' => bin2hex(random_bytes(12)),
            ],
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'sellableCountry' => [
                'code' => 'FR',
            ],
            'status' => 'active',
            'type' => 'mev',
            'productPeopleNumber' => '2',
        ];

        return $overrides + $payload;
    }

    public function getDefaultExperienceProduct(array $overrides = []): array
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'Test Experience',
            'description' => 'Test Experience Description',
            'isSellable' => true,
            'isReservable' => true,
            'partner' => [
                'id' => bin2hex(random_bytes(12)),
            ],
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'sellableCountry' => [
                'code' => 'FR',
            ],
            'status' => 'active',
            'type' => 'experience',
            'productDuration' => 2,
            'productDurationUnit' => 'Nights',
            'listPrice' => [
                'currencyCode' => 'EUR',
                'amount' => 100,
            ],
        ];

        return $overrides + $payload;
    }

    public function getDefaultComponentProduct(array $overrides = []): array
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'name' => 'Test Component 1',
            'description' => 'Test Component Description 1',
            'isSellable' => true,
            'isReservable' => true,
            'partner' => [
                'id' => bin2hex(random_bytes(12)),
            ],
            'sellableBrand' => [
                'code' => 'SBX',
            ],
            'sellableCountry' => [
                'code' => 'FR',
            ],
            'status' => 'active',
            'type' => 'component',
            'productDuration' => 2,
            'productDurationUnit' => 'Nights',
            'roomStockType' => 'stock',
            'listPrice' => [
              'currencyCode' => 'EUR',
              'amount' => 100,
          ],
        ];

        return $overrides + $payload;
    }

    public function getDefaultPartner(array $overrides = []): array
    {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'status' => 'new partner',
            'currencyCode' => 'EUR',
        ];

        return $overrides + $payload;
    }

    public function getDefaultBoxExperienceRelationship(array $overrides = []): array
    {
        $payload = [
            'parentProduct' => 'BB0000335658',
            'childProduct' => 'HG0000335654',
            'isEnabled' => true,
            'relationshipType' => 'Box-Experience',
        ];

        return $overrides + $payload;
    }

    public function getDefaultExperienceComponentRelationship(array $overrides = []): array
    {
        $payload = [
            'parentProduct' => 'HG0000335654',
            'childProduct' => 'CM0000335658',
            'isEnabled' => true,
            'relationshipType' => 'Experience-Component',
        ];

        return $overrides + $payload;
    }

    public function getDefaultPriceInformation(array $overrides = []): array
    {
        $payload = [
            'product' => [
                'id' => bin2hex(random_bytes(12)),
            ],
            'averageValue' => [
                'amount' => 100.00,
                'currencyCode' => 'EUR',
            ],
            'averageCommissionType' => 'amount',
            'averageCommission' => 7.50,
        ];

        return $overrides + $payload;
    }

    public function getDefaultRoomAvailability(array $overrides = []): array
    {
        $payload = [
            [
                'product' => [
                    'id' => '315172',
                ],
                'quantity' => 2,
                'dateFrom' => '2020-07-16T20:00:00.000000+0000',
                'dateTo' => '2020-07-20T20:00:00.000000+0000',
                'updatedAt' => '2020-07-20T17:58:32.000000+0000',
            ],
        ];

        return $overrides + $payload;
    }

    public function getDefaultRoomPrice(array $overrides = []): array
    {
        $payload = [
            [
                'product' => [
                    'id' => '315618',
                ],
                'price' => [
                    'amount' => 20.00,
                    'currencyCode' => 'EUR',
                ],
                'dateFrom' => '2020-07-16T20:00:00.000000+0000',
                'dateTo' => '2020-07-20T20:00:00.000000+0000',
                'updatedAt' => '2020-07-20T17:58:32.000000+0000',
            ],
        ];

        return $overrides + $payload;
    }

    /**
     * @return JsonResponse|object
     */
    public function testBoxProduct(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultBoxProduct();
        }
        $this->client->request('POST', $this->baseUrl.self::API_PRODUCT_BASE_URL, [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function testExperienceProduct(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultExperienceProduct();
        }
        $this->client->request('POST', $this->baseUrl.self::API_PRODUCT_BASE_URL, [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function testComponentProduct(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultComponentProduct();
        }
        $this->client->request('POST', $this->baseUrl.self::API_PRODUCT_BASE_URL, [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function testPartners(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultPartner();
        }
        $this->client->request('POST',
            $this->baseUrl.self::API_PARTNER_BASE_URL,
            [],
            [],
            [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function testExperienceComponentRelationship(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultExperienceComponentRelationship();
        }
        $this->client->request('POST',
            $this->baseUrl.self::API_RELATIONSHIP_BASE_URL,
            [],
            [],
            [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function testBoxExperienceRelationship(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultBoxExperienceRelationship();
        }
        $this->client->request('POST',
            $this->baseUrl.self::API_RELATIONSHIP_BASE_URL,
            [],
            [],
            [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function testPriceInformation(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultPriceInformation();
        }
        $this->client->request(
            'POST',
            $this->baseUrl.self::API_PRICE_INFORMATION_BASE_URL,
            [],
            [],
            [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function testRoomAvailability(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultRoomAvailability();
        }

        $this->client->request(
            'POST',
            $this->baseUrl.self::API_ROOM_AVAILABILITY_BASE_URL,
            [],
            [],
            [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function testRoomPrice(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultRoomPrice();
        }

        $this->client->request(
            'POST',
            $this->baseUrl.self::API_ROOM_PRICE_BASE_URL,
            [],
            [],
            [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }
}
