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

    protected AbstractBrowser $client;
    protected Serializer $serializer;
    protected ?string $baseUrl = null;

    public function __construct(AbstractBrowser $client, Serializer $serializer, ?string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
    }

    public function getDefaultProduct(array $overrides = []): array
    {
        $payload = [
            'uuid' => '3fa85f64-5717-4562-b3fc-2c963f66afa6',
            'id' => bin2hex(random_bytes(12)),
            'name' => 'product name',
            'description' => 'product description',
            'universe' => [
                'id' => 'product universe',
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
            'voucherExpirationDuration' => 3,
            'roomStockType' => 'on_request',
            'stockAllotment' => 5,
        ];

        return $overrides + $payload;
    }

    public function getDefaultPartner(array $overrides = []): array
    {
        $payload = [
            'uuid' => '3fa85f64-5717-4562-b3fc-2c963f66afa6',
            'id' => bin2hex(random_bytes(12)),
            'status' => 'new partner',
            'currencyCode' => 'USD',
            'isChannelManagerEnabled' => true,
            'partnerCeaseDate' => '2020-03-18',
        ];

        return $overrides + $payload;
    }

    public function getDefaultRelationship(array $overrides = []): array
    {
        $payload = [
            'parentProduct' => 'BB0000335658',
            'childProduct' => 'HG0000335654',
            'sortOrder' => 1,
            'isEnabled' => true,
            'relationshipType' => 'Box-Experience',
            'printType' => 'Digital',
            'childCount' => 4,
            'childQuantity' => 0,
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
            ],
            'averageCommissionType' => 'amount',
            'averageCommission' => 7.50,
        ];

        return $overrides + $payload;
    }

    /**
     * @return JsonResponse|object
     */
    public function testProducts(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultProduct();
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
    public function testRelationships(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefaultRelationship();
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
    public function testPriceInformations(array $payload = [])
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
}
