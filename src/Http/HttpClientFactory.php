<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;

class HttpClientFactory
{
    protected EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function buildWithOptions(string $clientId, array $options): HttpClient
    {
        return new HttpClient($clientId, $this->dispatcher, SymfonyHttpClient::create($options));
    }
}
