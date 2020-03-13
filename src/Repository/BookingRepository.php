<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;
use App\Exception\Repository\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Booking find($id, $lockMode = null, $lockVersion = null)
 * @method null|Booking findOneBy(array $criteria, array $orderBy = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findOne(string $uuid): Booking
    {
        $booking = $this->find($uuid);

        if (null === $booking) {
            throw new EntityNotFoundException();
        }

        return $booking;
    }
}
