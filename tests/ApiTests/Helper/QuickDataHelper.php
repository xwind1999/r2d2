<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class QuickDataHelper
{
    const API_BASE_URL = '/quickdata/GetPackage/1/12345';
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
    public function getPackage(int $packageCode, string $dateFrom, string $dateTo)
    {
        $this->client->request(
            'GET',
            sprintf(
                '%s?PackageCode=%s&dateFrom=%s&dateTo=%s',
                $this->baseUrl.self::API_BASE_URL,
                $packageCode,
                $dateFrom,
                $dateTo
            ),
            [],
            [],
            []
        );

        return $this->client->getResponse();
    }
}
