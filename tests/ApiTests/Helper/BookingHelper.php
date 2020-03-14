<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use App\Tests\ApiTests\ApiTestCase;
use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class BookingHelper
{
    const API_BASE_URL = '/api/booking';
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
            'partner_golden_id' => '5678',
            'experience_golden_id' => '9012',
            'type' => 'booking',
            'voucher' => '123456789',
            'brand' => 'sbx',
            'country' => 'fr',
            'request_type' => 'instant',
            'channel' => 'web',
            'cancellation_channel' => null,
            'status' => 'complete',
            'total_price' => 150,
            'start_date' => '2020-05-05',
            'end_date' => '2020-05-06',
            'customer_external_id' => 'W123123',
            'customer_first_name' => null,
            'customer_last_name' => null,
            'customer_email' => null,
            'customer_phone' => null,
            'customer_comment' => null,
            'partner_comment' => null,
            'placed_at' => '2020-01-01T00:00:00+0',
            'cancelled_at' => null,
        ];

        return $overrides + $payload;
    }

    public function addValidExperience(array &$payload)
    {
        $experience = json_decode(ApiTestCase::$experienceHelper->create()->getContent());
        $payload['experience_golden_id'] = $experience->golden_id;
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
            $this->addValidExperience($payload);
            $this->addValidPartner($payload);
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
