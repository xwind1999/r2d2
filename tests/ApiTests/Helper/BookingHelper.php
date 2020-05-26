<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class BookingHelper
{
    const API_BASE_URL = '/booking';
    protected AbstractBrowser $client;
    protected Serializer $serializer;
    protected ?string $baseUrl = null;

    public function __construct(AbstractBrowser $client, Serializer $serializer, ?string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
    }

    /**
     * @return JsonResponse|object
     */
    public function create(array $payload)
    {
        return $this->request('POST', $this->baseUrl.self::API_BASE_URL, $payload);
    }

    /**
     * @return JsonResponse|object
     */
    public function update(array $payload)
    {
        return $this->request('PATCH', $this->baseUrl.self::API_BASE_URL, $payload);
    }

    public function request(string $method, string $url, array $body)
    {
        $this->client->request($method, $url, [], [], [], $this->serializer->serialize($body, 'json'));

        return $this->client->getResponse();
    }
}
