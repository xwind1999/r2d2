<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Tests\ApiTests\Helper\BookingHelper;
use App\Tests\ApiTests\Helper\BoxExperienceHelper;
use App\Tests\ApiTests\Helper\BoxHelper;
use App\Tests\ApiTests\Helper\BroadcastListenerHelper;
use App\Tests\ApiTests\Helper\ComponentHelper;
use App\Tests\ApiTests\Helper\ExperienceComponentHelper;
use App\Tests\ApiTests\Helper\ExperienceHelper;
use App\Tests\ApiTests\Helper\HealthCheckHelper;
use App\Tests\ApiTests\Helper\PartnerHelper;
use App\Tests\ApiTests\Helper\RoomAvailabilityHelper;
use App\Tests\ApiTests\Helper\RoomPriceHelper;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class ApiTestCase extends WebTestCase
{
    protected static ?Serializer $serializer;

    protected static AbstractBrowser $client;

    public static PartnerHelper $partnerHelper;

    public static BroadcastListenerHelper $broadcastListenerHelper;

    public static ExperienceHelper $experienceHelper;

    public static ComponentHelper $componentHelper;

    public static RoomPriceHelper $roomPriceHelper;

    public static RoomAvailabilityHelper $roomAvailabilityHelper;

    public static BoxHelper $boxHelper;

    public static BookingHelper $bookingHelper;

    public static BoxExperienceHelper $boxExperienceHelper;

    public static ExperienceComponentHelper $experienceComponentHelper;

    public static HealthCheckHelper $healthCheckHelper;

    public static ?string $baseUrl = null;

    public static function setUpBeforeClass(): void
    {
        if (isset($_SERVER['API_TEST_BASE_URL'])) {
            static::$baseUrl = $_SERVER['API_TEST_BASE_URL'];
            static::$client = new HttpBrowser(HttpClient::create());
            static::$kernel = static::bootKernel([]);
        } else {
            static::$client = static::createClient();
        }

        static::$serializer = static::$kernel->getContainer()->get('jms_serializer');

        static::initHelpers();
    }

    public static function initHelpers(): void
    {
        static::$partnerHelper = new PartnerHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$broadcastListenerHelper = new BroadcastListenerHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$experienceHelper = new ExperienceHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$componentHelper = new ComponentHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$roomPriceHelper = new RoomPriceHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$roomAvailabilityHelper = new RoomAvailabilityHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$boxHelper = new BoxHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$bookingHelper = new BookingHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$boxExperienceHelper = new BoxExperienceHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$experienceComponentHelper = new ExperienceComponentHelper(clone static::$client, static::$serializer, static::$baseUrl);
        static::$healthCheckHelper = new HealthCheckHelper(clone static::$client, static::$serializer, static::$baseUrl);
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
