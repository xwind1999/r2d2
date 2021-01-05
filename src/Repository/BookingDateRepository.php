<?php

declare(strict_types=1);

namespace App\Repository;

use App\Constants\DateTimeConstants;
use App\Constraint\BookingStatusConstraint;
use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Exception\Repository\BookingDateNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findBookingDatesByExperiencesAndDates(
        array $experienceGoldenIds,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $qb = $this->createQueryBuilder('bd');
        $qb->select('b.experienceGoldenId, bd.componentGoldenId, bd.date, count(bd.componentGoldenId) as usedStock')
            ->join(
                Booking::class,
                'b',
                Join::WITH,
                'b.uuid = bd.booking 
                  AND b.expiredAt > :dateNow 
                  AND b.status = :status 
                  AND b.experienceGoldenId IN (:experienceGoldenIds)'
            )->where($qb->expr()->between('bd.date', ':startDate', ':endDate'))
            ->groupBy('b.experienceGoldenId, bd.componentGoldenId, bd.date');

        $qb->setParameter('experienceGoldenIds', $experienceGoldenIds, Connection::PARAM_STR_ARRAY);
        $qb->setParameter('startDate', $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT), \PDO::PARAM_STR);
        $qb->setParameter('endDate', $endDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT), \PDO::PARAM_STR);
        $qb->setParameter('dateNow', (new \DateTime('now'))->format(DateTimeConstants::DEFAULT_DATE_TIME_FORMAT), \PDO::PARAM_STR);
        $qb->setParameter('status', BookingStatusConstraint::BOOKING_STATUS_CREATED, \PDO::PARAM_STR);

        return $qb->getQuery()->getArrayResult();
    }
}
