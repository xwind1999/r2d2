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
        foreach (static::generateDates() as $date) {
            $this->connection->executeStatement(...$date);
        }
    }

    public static function getGroups(): array
    {
        return ['calendar'];
    }

    public static function generateDates(): array
    {
        $startDate = new \DateTime('2020-12-01');
        $endDate = new \DateTime('2040-12-31');
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);

        $dates = [];
        $queries = [['DELETE FROM calendar', []]];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
            if (count($dates) > 365) {
                $queries[] = static::insertDates($dates);
                $dates = [];
            }
        }
        if (count($dates) > 0) {
            $queries[] = static::insertDates($dates);
        }

        return $queries;
    }

    public static function insertDates(array $dates): array
    {
        $params = [];
        $countDates = count($dates);
        for ($i = 0; $i < $countDates; ++$i) {
            $params[] = '(?)';
        }

        return [sprintf('INSERT INTO calendar VALUES %s', implode(',', $params)), $dates];
    }
}
