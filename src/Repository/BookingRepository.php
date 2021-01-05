<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;
use App\Exception\Repository\BookingNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function save(Booking $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(Booking $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): Booking
    {
        $booking = $this->find($uuid);

        if (null === $booking) {
            throw new BookingNotFoundException();
        }

        return $booking;
    }

    public function findOneByGoldenId(string $goldenId): Booking
    {
        $booking = $this->findOneBy(['goldenId' => $goldenId]);

        if (null === $booking) {
            throw new BookingNotFoundException();
        }

        return $booking;
    }

    public function findListByGoldenId(array $goldenId): array
    {
        $booking = $this->findBy(['goldenId' => $goldenId]);

        if (empty($booking)) {
            throw new BookingNotFoundException();
        }

        return $booking;
    }
}
