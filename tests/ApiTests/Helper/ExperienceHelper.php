<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use App\Tests\ApiTests\ApiTestCase;
use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExperienceHelper
{
    const API_BASE_URL = '/api/experience';
    protected AbstractBrowser $client;
    protected Serializer $serializer;
    protected ?string $baseUrl = null;

    public function __construct(AbstractBrowser $client, Serializer $serializer, ?string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
    }

    public function getDefault(array $overrides = []): array
    {
        $payload = [
            'golden_id' => bin2hex(random_bytes(12)),
            'name' => '1234',
            'description' => '1234',
            'product_people_number' => '2',
            'voucher_expiration_duration' => 3,
            'partner_golden_id' => '1234',
        ];

        return $overrides + $payload;
    }

    public function addValidPartner(array &$payload)
    {
        $partner = json_decode(ApiTestCase::$partnerHelper->create()->getContent());
        $payload['partner_golden_id'] = $partner->golden_id;
    }

    /**
     * @return JsonResponse|object
     */
    public function create(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefault();
            $this->addValidPartner($payload);
        }

        $this->client->request('POST', $this->baseUrl.self::API_BASE_URL, [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function get(string $uuid)
    {
        $this->client->request('GET', sprintf('%s/%s', $this->baseUrl.self::API_BASE_URL, $uuid), [], [], []);

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function delete(string $uuid)
    {
        $this->client->request('DELETE', sprintf('%s/%s', $this->baseUrl.self::API_BASE_URL, $uuid), [], [], []);

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function update(string $uuid, array $payload)
    {
        $this->client->request('PUT', sprintf('%s/%s', $this->baseUrl.self::API_BASE_URL, $uuid), [], [], [], $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }
}
