<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;

class CalendarFixture extends Fixture implements FixtureGroupInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function load(?ObjectManager $manager): void
    {
        foreach (CalendarFixtureHelper::generateDates() as $date) {
            $this->connection->executeStatement(...$date);
        }
    }

    public static function getGroups(): array
    {
        return ['calendar'];
    }
}
