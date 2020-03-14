<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class PartnerHelper
{
    const API_BASE_URL = '/api/partner';
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
            'golden_id' => bin2hex(random_bytes(12)),
            'status' => 'active',
            'currency' => 'EUR',
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
    public function get(string $uuid)
    {
        $this->client->request('GET', sprintf('%s/%s', self::API_BASE_URL, $uuid), [], [], []);

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function delete(string $uuid)
    {
        $this->client->request('DELETE', sprintf('%s/%s', self::API_BASE_URL, $uuid), [], [], []);

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function update(string $uuid, array $payload)
    {
        $this->client->request('PUT', sprintf('%s/%s', self::API_BASE_URL, $uuid), [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }
}
