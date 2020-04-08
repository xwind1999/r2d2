<?php

declare(strict_types=1);

namespace App\QuickData;

use App\Http\HttpClient;
use App\Http\HttpClientFactory;

class QuickData
{
    protected const CLIENT_ID = 'quickdata';
    protected const ENGINE_ID = '9D712592-92DD-4824-8FAD-7459CF953A01';
    protected const GET_PACKAGE_ENDPOINT = 'GetPackage/1/%s';
    protected const GET_PACKAGE_V2_ENDPOINT = 'GetPackageV2/1/%s';
    protected const GET_RANGE_ENDPOINT = 'GetRangeV2/1/%s';
    protected const AVAILABILITY_PRICE_PERIOD_ENDPOINT = 'availabilitypriceperiod/1/%s';

    protected HttpClient $httpClient;

    public function __construct(array $options, HttpClientFactory $httpClientFactory)
    {
        $this->httpClient = $httpClientFactory->buildWithOptions(self::CLIENT_ID, $options);
    }

    public function getPackage(int $packageCode, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        $q = $this->httpClient->request('GET', sprintf(self::GET_PACKAGE_ENDPOINT, self::ENGINE_ID), [
            'format' => 'json',
            'PackageCode' => (string) $packageCode,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);

        return $q->toArray();
    }

    public function getRange(int $boxId, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        $q = $this->httpClient->request('GET', sprintf(self::GET_RANGE_ENDPOINT, self::ENGINE_ID), [
            'format' => 'json',
            'boxVersion' => (string) $boxId,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);

        return $q->toArray();
    }

    /**
     * @param array<int, int> $packageCodes
     */
    public function getPackageV2(array $packageCodes, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        $q = $this->httpClient->request('GET', sprintf(self::GET_PACKAGE_V2_ENDPOINT, self::ENGINE_ID), [
            'format' => 'json',
            'listPackageCode' => implode(',', $packageCodes),
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);

        return $q->toArray();
    }

    public function availabilityPricePeriod(int $prestid, \DateTimeInterface $dateFrom, \DateTimeInterface $dateTo): array
    {
        $q = $this->httpClient->request('GET', sprintf(self::AVAILABILITY_PRICE_PERIOD_ENDPOINT, self::ENGINE_ID), [
            'format' => 'json',
            'prestid' => $prestid,
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ]);

        return $q->toArray();
    }
}
