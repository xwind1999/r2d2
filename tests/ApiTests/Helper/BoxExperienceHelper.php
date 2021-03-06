<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use App\Tests\ApiTests\ApiTestCase;
use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class BoxExperienceHelper
{
    const API_BASE_URL = '/internal/box-experience';
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

    public function addValidExperience(array &$payload)
    {
        $experience = json_decode(ApiTestCase::$experienceHelper->create()->getContent());
        $payload['experienceGoldenId'] = $experience->goldenId;
    }

    public function addValidBox(array &$payload)
    {
        $box = json_decode(ApiTestCase::$boxHelper->create()->getContent());
        $payload['boxGoldenId'] = $box->goldenId;
    }

    /**
     * @return JsonResponse|object
     */
    public function create(array $payload = [])
    {
        if (empty($payload)) {
            $payload = $this->getDefault();
            $this->addValidExperience($payload);
            $this->addValidBox($payload);
        }

        $this->client->request(
            'POST',
            $this->baseUrl.self::API_BASE_URL, [], [], [],
            $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function delete(string $boxGoldenId, string $experienceGoldenId)
    {
        $payload = [
            'experienceGoldenId' => $experienceGoldenId,
            'boxGoldenId' => $boxGoldenId,
        ];

        $this->client->request(
            'DELETE',
            $this->baseUrl.self::API_BASE_URL, [], [], [],
            $this->serializer->serialize($payload, 'json'));

        return $this->client->getResponse();
    }
}
