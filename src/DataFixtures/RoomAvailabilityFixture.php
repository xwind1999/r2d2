<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\RoomAvailability;
use App\Repository\ComponentRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RoomAvailabilityFixture extends Fixture implements FixtureGroupInterface
{
    private const AVAILABILITY_DATES_RANGE = '+7 days';

    private ComponentRepository $componentRepository;

    public function __construct(ComponentRepository $componentRepository)
    {
        $this->componentRepository = $componentRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $components = $this->componentRepository->findAll();

        foreach ($components as $component) {
            $calendar = $this->getAvailabilityCalendar();
            foreach ($calendar as $date => $availability) {
                $roomAvailability = new RoomAvailability();
                $roomAvailability->component = $component;
                $roomAvailability->componentGoldenId = $component->goldenId;
                $roomAvailability->type = $component->roomStockType ?? '';
                $roomAvailability->date = new \DateTime($date);
                $roomAvailability->stock = $availability;
                $manager->persist($roomAvailability);
            }
        }
        $manager->flush();
    }

    public function getAvailabilityCalendar(): array
    {
        $beginDate = new \DateTime('2020-09-01');
        $endDate = (clone $beginDate)->modify(self::AVAILABILITY_DATES_RANGE);
        $endDate = $endDate->modify('+1 day');

        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($beginDate, $interval, $endDate);

        $calendar = [];
        foreach ($dateRange as $date) {
            $calendar[$date->format('Y-m-d')] = random_int(0, 9) < 2 ? 0 : 1;
        }

        return $calendar;
    }

    public static function getGroups(): array
    {
        return ['room'];
    }
}
