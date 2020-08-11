<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\RoomPrice;
use App\Repository\ComponentRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RoomPriceFixture extends Fixture implements FixtureGroupInterface
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
                $roomPrice = new RoomPrice();
                $roomPrice->component = $component;
                $roomPrice->componentGoldenId = $component->goldenId;
                $roomPrice->price = random_int(45, 85);
                $roomPrice->date = new \DateTime($date);
                $manager->persist($roomPrice);
            }
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['room'];
    }
}
