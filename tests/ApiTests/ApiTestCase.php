<?php

declare(strict_types=1);

namespace App\Tests\ApiTests;

use App\Tests\ApiTests\Helper\BookingDateHelper;
use App\Tests\ApiTests\Helper\BookingHelper;
use App\Tests\ApiTests\Helper\BoxExperienceHelper;
use App\Tests\ApiTests\Helper\BoxHelper;
use App\Tests\ApiTests\Helper\ExperienceHelper;
use App\Tests\ApiTests\Helper\PartnerHelper;
use App\Tests\ApiTests\Helper\ProductHelper;
use App\Tests\ApiTests\Helper\RateBandHelper;
use App\Tests\ApiTests\Helper\RoomAvailabilityHelper;
use App\Tests\ApiTests\Helper\RoomHelper;
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

    public static ProductHelper $productHelper;

    public static ExperienceHelper $experienceHelper;

    public static RateBandHelper $rateBandHelper;

    public static RoomHelper $roomHelper;

    public static RoomPriceHelper $roomPriceHelper;

    public static RoomAvailabilityHelper $roomAvailabilityHelper;

    public static BoxHelper $boxHelper;

    public static BookingDateHelper $bookingDateHelper;

    public static BookingHelper $bookingHelper;

    public static BoxExperienceHelper $boxExperienceHelper;

    public static function setUpBeforeClass(): void
    {
        if (isset($_ENV['API_TEST_BASE_URL'])) {
            static::$client = new HttpBrowser(HttpClient::createForBaseUri($_ENV['API_TEST_BASE_URL']));
        } else {
            static::$client = static::createClient();
        }

        static::$serializer = static::$kernel->getContainer()->get('jms_serializer');

        static::initHelpers();
    }

    public static function initHelpers(): void
    {
        static::$partnerHelper = new PartnerHelper(clone static::$client, static::$serializer);
        static::$productHelper = new ProductHelper(clone static::$client, static::$serializer);
        static::$experienceHelper = new ExperienceHelper(clone static::$client, static::$serializer);
        static::$rateBandHelper = new RateBandHelper(clone static::$client, static::$serializer);
        static::$roomHelper = new RoomHelper(clone static::$client, static::$serializer);
        static::$roomPriceHelper = new RoomPriceHelper(clone static::$client, static::$serializer);
        static::$roomAvailabilityHelper = new RoomAvailabilityHelper(clone static::$client, static::$serializer);
        static::$boxHelper = new BoxHelper(clone static::$client, static::$serializer);
        static::$bookingDateHelper = new BookingDateHelper(clone static::$client, static::$serializer);
        static::$bookingHelper = new BookingHelper(clone static::$client, static::$serializer);
        static::$boxExperienceHelper = new BoxExperienceHelper(clone static::$client, static::$serializer);
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
