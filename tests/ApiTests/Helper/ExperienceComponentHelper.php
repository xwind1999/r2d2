<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use App\Tests\ApiTests\ApiTestCase;
use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;

class ExperienceComponentHelper
{
    const API_BASE_URL = '/internal/experience-component';

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
            'isEnabled' => true,
            'externalUpdatedAt' => '2020-01-01T00:00:00+0',
        ];

        return $overrides + $payload;
    }

    public function create(array $payload = []): object
    {
        if (empty($payload)) {
            $payload = $this->getDefault();
            $this->addValidExperience($payload);
            $this->addValidComponent($payload);
        }

        $this->client->request(
            'POST',
            $this->baseUrl.self::API_BASE_URL, [], [], [],
            $this->serializer->serialize($payload, 'json')
        );

        return $this->client->getResponse();
    }

    public function delete(string $componentGoldenId, string $experienceGoldenId): object
    {
        $payload = [
            'experienceGoldenId' => $experienceGoldenId,
            'componentGoldenId' => $componentGoldenId,
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

        $payload['experienceGoldenId'] = $experience->goldenId;
    }

    private function addValidComponent(array &$payload): void
    {
        $component = json_decode(ApiTestCase::$componentHelper->create()->getContent());

        $payload['componentGoldenId'] = $component->goldenId;
    }
}
