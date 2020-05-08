<?php

declare(strict_types=1);

namespace App\Tests\ApiTests\Helper;

use JMS\Serializer\Serializer;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckHelper
{
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
     * @return object|Response
     */
    public function ping()
    {
        $this->client->request('GET', sprintf('%s/ping', $this->baseUrl), [], [], []);

        return $this->client->getResponse();
    }
}
