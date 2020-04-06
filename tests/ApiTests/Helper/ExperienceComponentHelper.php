<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use App\Tests\ApiTests\ApiTestCase;
use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;

class ExperienceComponentHelper
{
    const API_BASE_URL = '/api/experience-component';

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
            'is_enabled' => true,
            'external_updated_at' => '2020-01-01T00:00:00+0',
        ];

        return $overrides + $payload;
    }

    public function create(array $payload = []): object
    {
        if (empty($payload)) {
            $payload = $this->getDefault();
            $this->addValidExperience($payload);
            $this->addValidRoom($payload);
        }

        $this->client->request(
            'POST',
            $this->baseUrl.self::API_BASE_URL, [], [], [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }

    public function delete(string $roomGoldenId, string $experienceGoldenId): object
    {
        $payload = [
            'experience_golden_id' => $experienceGoldenId,
            'room_golden_id' => $roomGoldenId,
        ];

        $this->client->request('DELETE',
            $this->baseUrl.self::API_BASE_URL, [], [], [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }

    private function addValidExperience(array &$payload): void
    {
        $experience = json_decode(ApiTestCase::$experienceHelper->create()->getContent());

        $payload['experience_golden_id'] = $experience->golden_id;
    }

    private function addValidRoom(array &$payload): void
    {
        $room = json_decode(ApiTestCase::$roomHelper->create()->getContent());

        $payload['room_golden_id'] = $room->golden_id;
    }
}
