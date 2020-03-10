<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class ApiTestCase extends WebTestCase
{
    protected static ?Serializer $serializer;

    protected static AbstractBrowser $client;

    public static function setUpBeforeClass(): void
    {
        if (isset($_ENV['API_TEST_BASE_URL'])) {
            static::$client = new HttpBrowser(HttpClient::createForBaseUri($_ENV['API_TEST_BASE_URL']));
        } else {
            static::$client = static::createClient();
        }

        static::$serializer = static::$kernel->getContainer()->get('jms_serializer');
    }

    /**
     * @param mixed|object $data
     */
    protected function serialize($data): string
    {
        return static::$serializer->serialize($data, 'json');
    }

    protected function client(): AbstractBrowser
    {
        return static::$client;
    }
}
