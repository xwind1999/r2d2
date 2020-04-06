<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class BroadcastListenerHelper
{
    const API_PRODUCT_BASE_URL = '/api/broadcast-listener/product';
    const API_PARTNER_BASE_URL = '/api/broadcast-listener/partner';
    const API_RELATIONSHIP_BASE_URL = '/api/broadcast-listener/relationship';
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
            'golden_id' => bin2hex(random_bytes(12)),
            'name' => 'product name',
            'description' => 'product description',
            'universe' => 'product universe',
            'is_sellable' => true,
            'is_reservable' => true,
            'partner_golden_id' => bin2hex(random_bytes(12)),
        ];

        return $overrides + $payload;
    }

    public function getDefaultPartner(array $overrides = []): array
    {
        $payload = [
            'uuid' => '3fa85f64-5717-4562-b3fc-2c963f66afa6',
            'golden_id' => bin2hex(random_bytes(12)),
            'status' => 'alive',
            'currency' => 'USD',
            'ceased_date' => '2020-03-18',
        ];

        return $overrides + $payload;
    }

    public function getDefaultRelationship(array $overrides = []): array
    {
        $payload = [
            'parent_product' => 'BB0000335658',
            'child_product' => 'HG0000335654',
            'sort_order' => 1,
            'is_enabled' => true,
            'relationship_type' => 'Box-Experience',
            'print_type' => 'Digital',
            'child_count' => 4,
            'child_quantity' => 0,
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
        $this->client->request('POST', $this->baseUrl.self::API_PARTNER_BASE_URL, [], [], [], $this->serializer->serialize($payload, 'json'));

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
        $this->client->request('POST', $this->baseUrl.self::API_RELATIONSHIP_BASE_URL, [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }
}
