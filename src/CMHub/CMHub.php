<?php

declare(strict_types=1);

namespace App\CMHub;

use App\Http\HttpClient;
use App\Http\HttpClientFactory;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CMHub
{
    protected const CLIENT_ID = 'cmub';
    protected const GET_AVAILABILITY = 'r2d2/availability/%s';

    protected HttpClient $httpClient;

    public function __construct(array $options, HttpClientFactory $httpClientFactory)
    {
        $this->httpClient = $httpClientFactory->buildWithOptions(self::CLIENT_ID, $options);
    }

    public function getAvailability(
        int $productId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): ResponseInterface {
        return $this->httpClient->request(
            'GET',
            sprintf(self::GET_AVAILABILITY, $productId),
            [
                'start' => $dateFrom->format('Y-m-d'),
                'end' => $dateTo->format('Y-m-d'),
            ]
        );
    }
}
