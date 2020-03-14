<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BookingDate;
use App\Exception\Repository\BookingDateNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BookingDate[]    findAll()
 * @method BookingDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|BookingDate find($id, $lockMode = null, $lockVersion = null)
 * @method null|BookingDate findOneBy(array $criteria, array $orderBy = null)
 */
class BookingDateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookingDate::class);
    }

    public function save(BookingDate $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(BookingDate $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): BookingDate
    {
        $bookingDate = $this->find($uuid);

        if (null === $bookingDate) {
            throw new BookingDateNotFoundException();
        }

        return $bookingDate;
    }
}
