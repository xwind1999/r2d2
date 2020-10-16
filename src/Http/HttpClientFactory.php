<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\CorrelationId\CorrelationId;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HttpClientFactory
{
    protected EventDispatcherInterface $dispatcher;

    protected CorrelationId $correlationId;

    public function __construct(EventDispatcherInterface $dispatcher, CorrelationId $correlationId)
    {
        $this->dispatcher = $dispatcher;
        $this->correlationId = $correlationId;
    }

    public function buildWithOptions(string $clientId, array $options): HttpClient
    {
        $options['headers'][CorrelationId::HEADER_KEY] = $this->correlationId->getCorrelationId();

        return new HttpClient($clientId, $this->dispatcher, SymfonyHttpClient::create($options));
    }
}
