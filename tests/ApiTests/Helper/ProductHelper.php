<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductHelper
{
    const API_BASE_URL = '/broadcast-listeners/products';
    protected AbstractBrowser $client;
    protected Serializer $serializer;

    public function __construct(AbstractBrowser $client, Serializer $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    public function getDefault(array $overrides = []): array
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

    /**
     * @return JsonResponse|object
     */
    public function create(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefault();
        }
        $this->client->request('POST', self::API_BASE_URL, [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function update(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefault();
        }
        $this->client->request('PUT', self::API_BASE_URL, [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }
}
