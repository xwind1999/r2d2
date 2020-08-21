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
    private ComponentRepository $componentRepository;

    public function __construct(ComponentRepository $componentRepository)
    {
        $this->componentRepository = $componentRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $components = $this->componentRepository->findBy(['isReservable' => true, 'roomStockType' => ['stock', 'on_request']]);

        foreach ($components as $component) {
            $calendar = CalendarFixtureHelper::getAvailabilityCalendar();
            foreach ($calendar as $date => $availability) {
                $roomAvailability = new RoomAvailability();
                $roomAvailability->component = $component;
                $roomAvailability->componentGoldenId = $component->goldenId;
                $roomAvailability->type = $component->roomStockType ?? '';
                $roomAvailability->isStopSale = false;
                $roomAvailability->date = new \DateTime($date);
                $roomAvailability->stock = $availability;
                $manager->persist($roomAvailability);
            }
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['room'];
    }
}
