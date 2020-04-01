<?php

declare(strict_types=1);

namespace App\QuickData;

use App\Http\HttpClient;
use App\Http\HttpClientFactory;

class QuickData
{
    protected const CLIENT_ID = 'quickdata';
    protected const ENGINE_ID = '9D712592-92DD-4824-8FAD-7459CF953A01';

    protected HttpClient $httpClient;

    public function __construct(array $options, HttpClientFactory $httpClientFactory)
    {
        $this->httpClient = $httpClientFactory->buildWithOptions(self::CLIENT_ID, $options);
    }

    public function getPackage(int $packageCode, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        $q = $this->httpClient->request('GET', sprintf('GetPackage/1/%s', self::ENGINE_ID), [
            'format' => 'json',
            'PackageCode' => (string) $packageCode,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);

        return $q->toArray();
    }
}
