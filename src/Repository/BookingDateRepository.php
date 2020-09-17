<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\BookingDate;
use App\Exception\Repository\BookingDateNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
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

    public function findBookingDatesByExperiencesAndDate(
        array $experienceGoldenIds,
        \DateTimeInterface $startDate): array
    {
        $sql = <<<SQL
SELECT
   b.experience_golden_id as experienceGoldenId,
   bd.component_golden_id as componentGoldenId, 
   bd.date AS date,
   count(bd.component_golden_id) AS usedStock
FROM
   r2d2.booking_date bd
   INNER JOIN
      r2d2.booking b
      ON (b.uuid = bd.booking_uuid) 
   INNER JOIN
      r2d2.flat_manageable_component f 
      ON (f.experience_golden_id = b.experience_golden_id
      AND f.component_uuid = bd.component_uuid) 
 WHERE
    b.expired_at > :dateNow
   AND b.experience_golden_id IN (:experienceGoldenIds)
   AND bd.date BETWEEN :startDate AND date_add(:startDate, interval f.duration - 1 day)
GROUP BY
   b.experience_golden_id,
   bd.component_golden_id,
   bd.date
SQL;
        $params = [
            'experienceGoldenIds' => $experienceGoldenIds,
            'startDate' => $startDate->format('Y-m-d'),
            'dateNow' => (new \DateTime('now'))->format('Y-m-d'),
        ];
        $types = [
            'experienceGoldenIds' => Connection::PARAM_STR_ARRAY,
            'startDate' => \PDO::PARAM_STR,
            'dateNow' => \PDO::PARAM_STR,
        ];

        try {
            return ($this->_em->getConnection()->executeQuery($sql, $params, $types))->fetchAll();
        } catch (DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
