<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use App\Tests\ApiTests\ApiTestCase;
use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class BookingDateHelper
{
    const API_BASE_URL = '/internal/booking-date';
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
            'booking_golden_id' => '1234',
            'room_golden_id' => '1123',
            'rate_band_golden_id' => '12',
            'date' => '2020-01-01',
            'price' => 9990,
            'is_upsell' => true,
            'guests_count' => 1,
        ];

        return $overrides + $payload;
    }

    public function addValidBooking(array &$payload)
    {
        $booking = json_decode(ApiTestCase::$bookingHelper->create()->getContent());
        $payload['booking_golden_id'] = $booking->golden_id;
    }

    public function addValidRoom(array &$payload)
    {
        $room = json_decode(ApiTestCase::$roomHelper->create()->getContent());
        $payload['room_golden_id'] = $room->golden_id;
    }

    public function addValidRateBand(array &$payload)
    {
        $rateBand = json_decode(ApiTestCase::$rateBandHelper->create()->getContent());
        $payload['rate_band_golden_id'] = $rateBand->golden_id;
    }

    /**
     * @return JsonResponse|object
     */
    public function create(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefault();
            $this->addValidRoom($payload);
            $this->addValidRateBand($payload);
            $this->addValidBooking($payload);
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
