<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;

class QuickDataHelper
{
    const API_GETRANGE_V2 = '/quickdata/GetRangeV2/1/12345';
    const API_GETPACKAGE_V1 = '/quickdata/GetPackage/1/12345';
    const API_GETPACKAGE_V2 = '/quickdata/GetPackageV2/1/12345';
    const API_AVAILABILITY_PRICE_PERIOD = '/quickdata/availabilitypriceperiod/1/12345';
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
    public function getPackage(string $packageCode, string $dateFrom, string $dateTo)
    {
        $this->client->request(
            'GET',
            sprintf(
                '%s?PackageCode=%s&dateFrom=%s&dateTo=%s',
                $this->baseUrl.self::API_GETPACKAGE_V1,
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

    /**
     * @return JsonResponse|object
     */
    public function getPackageV2(string $packageCodes, string $dateFrom, string $dateTo)
    {
        $this->client->request(
            'GET',
            sprintf(
                '%s?ListPackageCode=%s&dateFrom=%s&dateTo=%s',
                $this->baseUrl.self::API_GETPACKAGE_V2,
                $packageCodes,
                $dateFrom,
                $dateTo
            ),
            [],
            [],
            []
        );

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function availabilityPricePeriod(string $experienceId, string $dateFrom, string $dateTo)
    {
        $this->client->request(
            'GET',
            sprintf(
                '%s?ExperienceId=%s&prestid=1&datefrom=%s&dateto=%s',
                $this->baseUrl.self::API_AVAILABILITY_PRICE_PERIOD,
                $experienceId,
                $dateFrom,
                $dateTo
            ),
            [],
            [],
            []
        );

        return $this->client->getResponse();
    }

    /**
     * @return JsonResponse|object
     */
    public function getRangeV2(string $boxId, string $dateFrom)
    {
        $this->client->request(
            'GET',
            sprintf(
                '%s?boxVersion=%s&dateFrom=%s&dateTo=%s',
                $this->baseUrl.self::API_GETRANGE_V2,
                $boxId,
                $dateFrom,
                $dateFrom
            ),
            [],
            [],
            []
        );

        return $this->client->getResponse();
    }
}
