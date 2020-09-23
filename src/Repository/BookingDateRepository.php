<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Exception\Repository\BookingDateNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

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

    public function findBookingDatesByComponentAndDate(
        string $componentGoldenId,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo): array
    {
        $qb = $this->createQueryBuilder('bd');
        $qb->select('bd.componentGoldenId, bd.date, count(bd.componentGoldenId) as usedStock')
            ->join(Booking::class, 'b', Join::WITH, 'b.uuid = bd.booking')
            ->where('b.expiredAt > :dateNow')
            ->andWhere($qb->expr()->between('bd.date', ':dateFrom', ':dateTo'))
            ->andWhere($qb->expr()->eq('bd.componentGoldenId', ':component'))
            ->groupBy('bd.componentGoldenId, bd.date')
            ->setParameter('component', $componentGoldenId)
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d'))
            ->setParameter('dateTo', $dateTo->format('Y-m-d'))
            ->setParameter('dateNow', (new \DateTime('now'))->format('Y-m-d H:i:s'));

        return $qb->getQuery()->getResult();
    }
}
