<?php

declare(strict_types=1);

namespace App\Repository;

use App\Constraint\RoomStockTypeConstraint;
use App\Entity\Booking;
use App\Entity\Component;
use App\Entity\RoomAvailability;
use App\Exception\Repository\RoomAvailabilityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\QueryException;

/**
 * @method null|RoomAvailability find($id, $lockMode = null, $lockVersion = null)
 * @method null|RoomAvailability findOneBy(array $criteria, array $orderBy = null)
 * @method RoomAvailability[]    findAll()
 * @method RoomAvailability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomAvailabilityRepository extends ServiceEntityRepository
{
    private const CLEANUP_AVAILABILITY_OLDER_THAN = '7 days ago';
    private const AVAILABILITY_READ_DATABASE = 'availability_read';

    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        parent::__construct($registry, RoomAvailability::class);
    }

    public function save(RoomAvailability $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(RoomAvailability $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): RoomAvailability
    {
        $roomAvailability = $this->find($uuid);

        if (null === $roomAvailability) {
            throw new RoomAvailabilityNotFoundException();
        }

        return $roomAvailability;
    }

    public function findAvailableRoomsByBoxId(
        string $boxId,
        \DateTimeInterface $startDate
    ): array {
        $sql = <<<SQL
SELECT
   ar.experience_golden_id as experienceGoldenId, ar.room_stock_type as roomStockType
FROM flat_manageable_component ar
JOIN room_availability ra on ar.component_uuid = ra.component_uuid
WHERE ar.box_golden_id = :boxId AND
      ra.is_stop_sale = false AND
      (ar.last_bookable_date IS NULL OR ra.date <= (ar.last_bookable_date - INTERVAL ar.duration DAY)) AND 
      (((ra.type in (:stockType,:allotmentType)) and ra.stock > 0) OR (ra.type = :onRequestType)) AND
      ra.date BETWEEN :dateFrom AND DATE_ADD(:dateFrom, interval ar.duration - 1 day)
GROUP BY ar.experience_golden_id, ar.duration, ar.room_stock_type HAVING count(ra.date) = ar.duration;
SQL;

        $statement = $this->getAvailabilityReadOnlyConnection()->prepare($sql);
        $statement->bindValue('boxId', $boxId);
        $statement->bindValue('dateFrom', $startDate->format('Y-m-d'));
        $statement->bindValue('stockType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK);
        $statement->bindValue('onRequestType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST);
        $statement->bindValue('allotmentType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findAvailableRoomsByMultipleExperienceIds(
        array $experienceGoldenIds,
        \DateTimeInterface $startDate
    ): array {
        $sql = <<<SQL
SELECT fmc.experience_golden_id, fmc.partner_golden_id, fmc.is_sellable, fmc.duration, ra.date, ra.stock
FROM room_availability ra
JOIN (
    SELECT
        distinct component_uuid,
        component_golden_id,
        experience_golden_id,
        partner_golden_id,
        last_bookable_date,
        duration,
        room_stock_type,
        is_sellable
    FROM
        flat_manageable_component fmc
    WHERE
        fmc.experience_golden_id in (:experienceGoldenIds)) fmc
    ON ra.component_uuid = fmc.component_uuid
WHERE
    ra.is_stop_sale = false AND
    (fmc.last_bookable_date IS NULL OR ra.date <= (fmc.last_bookable_date - INTERVAL fmc.duration DAY)) AND
    ((ra.type in (:stockType,:allotmentType)) and ra.stock > 0) AND
     ra.date BETWEEN :startDate AND DATE_ADD(:startDate, interval fmc.duration - 1 day)
GROUP BY 
    fmc.experience_golden_id,
    fmc.partner_golden_id,
    fmc.is_sellable,
    ra.date,
    fmc.duration,
    fmc.room_stock_type,
    ra.stock  
HAVING count(ra.date) = fmc.duration;
SQL;
        $values = [
            'experienceGoldenIds' => $experienceGoldenIds,
            'startDate' => $startDate->format('Y-m-d'),
            'stockType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK,
            'allotmentType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT,
        ];
        $types = [
            'experienceGoldenIds' => Connection::PARAM_STR_ARRAY,
            'startDate' => \PDO::PARAM_STR,
            'stockType' => \PDO::PARAM_STR,
            'allotmentType' => \PDO::PARAM_STR,
        ];

        $query = $this->getEntityManager()->getConnection()->executeQuery($sql, $values, $types);

        return $query->fetchAll();
    }

    public function findRoomAvailabilitiesByMultipleComponentGoldenIds(
        array $componentIds,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $qb = $this->createQueryBuilder('r');
        $result = $qb
            ->select('r.stock, r.date, r.type, r.componentGoldenId')
            ->where($qb->expr()->in('r.componentGoldenId', $componentIds))
            ->andWhere('r.date BETWEEN :dateFrom AND :dateTo')
            ->orderBy('r.date', 'ASC')
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d'))
            ->setParameter('dateTo', $dateTo->format('Y-m-d'))
            ->getQuery()
            ->getArrayResult()
        ;

        $resultSet = [];
        foreach ($result as $item) {
            $date = $item['date']->format('Y-m-d');
            if (!isset($resultSet[$item['componentGoldenId']])) {
                $resultSet[$item['componentGoldenId']] = [];
            }
            $resultSet[$item['componentGoldenId']][$date] = $item;
        }

        return $resultSet;
    }

    /**
     * @throws QueryException
     */
    public function findRoomAvailabilitiesByComponent(
        Component $component,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r.stock, r.date, r.type, r.componentGoldenId, r.isStopSale')
            ->where('r.component = :component')
            ->andWhere('r.date BETWEEN :dateFrom AND :dateTo')
            ->orderBy('r.date', 'ASC')
            ->setParameter('component', $component->uuid->getBytes())
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d'))
            ->setParameter('dateTo', $dateTo->format('Y-m-d'))
            ->indexBy('r', 'r.date')
        ;

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @return RoomAvailability[]
     *
     * @throws QueryException
     */
    public function findByComponentAndDateRange(
        Component $component,
        \DateTime $dateFrom,
        \DateTime $dateTo
    ): array {
        $qb = $this->createQueryBuilder('ra');
        $qb
            ->where('ra.component = :component')
            ->andWhere('ra.date BETWEEN :dateFrom AND :dateTo')
            ->setParameter('component', $component->uuid->getBytes())
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d'))
            ->setParameter('dateTo', $dateTo->format('Y-m-d'))
            ->indexBy('ra', 'ra.date')
        ;

        return $qb->getQuery()->getResult();
    }

    public function cleanup(): void
    {
        $cleanUpOlderThan = (new \DateTime(self::CLEANUP_AVAILABILITY_OLDER_THAN))->setTime(0, 0, 0, 0);
        $this
            ->_em
            ->createQueryBuilder()
            ->delete(RoomAvailability::class, 'ra')
            ->where('ra.date < :oldAvailabilityDate')
            ->setParameter('oldAvailabilityDate', $cleanUpOlderThan->format('Y-m-d'))
            ->getQuery()
            ->execute()
        ;
    }

    public function getAvailabilityReadOnlyConnection(): Connection
    {
        $conn = $this->registry->getConnection(static::AVAILABILITY_READ_DATABASE);

        if ($conn instanceof Connection) {
            return $conn;
        }

        return $this->getEntityManager()->getConnection();
    }

    public function getAvailabilityByBookingAndDates(Booking $booking): array
    {
        $sql = <<<SQL
SELECT r.component_golden_id as componentGoldenId,
        sub.date,
        sum(r.stock) - coalesce(sub.usedStock, sub.usedStock, 0) as stock 
    FROM r2d2.room_availability r
        JOIN (
            SELECT bd.component_uuid, bd.date, count(*) as usedStock
                FROM r2d2.booking_date bd
                JOIN r2d2.booking b
                    ON  b.uuid = bd.booking_uuid
                WHERE b.status = :status
                    AND bd.date BETWEEN :dateFrom AND :dateTo
                    AND b.golden_id = :bookingGoldenId
                GROUP BY b.golden_id, bd.component_uuid, bd.date
        ) sub ON sub.component_uuid = r.component_uuid AND sub.date = r.date
    GROUP BY r.component_golden_id, sub.date, sub.usedStock;
SQL;
        $statement = $this->getAvailabilityReadOnlyConnection()->prepare($sql);
        $statement->bindValue('status', $booking->status);
        $statement->bindValue('bookingGoldenId', $booking->goldenId);
        $statement->bindValue('dateFrom', $booking->startDate->format('Y-m-d'));
        $statement->bindValue('dateTo', $booking->endDate->format('Y-m-d'));
        $statement->execute();

        return $statement->fetchAll();
    }

    public function updateStockByComponentAndDates(
        string $componentGoldenId,
        \DateTime $date
    ): int {
        $sql = <<<SQL
    UPDATE 
        room_availability 
    SET
        stock = IF(stock > 0, stock - 1, 0),
        updated_at = now() 
    WHERE component_golden_id = :componentGoldenId AND date = :date
SQL;
        $params = [
            'componentGoldenId' => $componentGoldenId,
            'date' => $date->format('Y-m-d'),
        ];

        return $this->_em->getConnection()->executeUpdate($sql, $params);
    }

    public function findAvailableRoomsAndPricesByExperienceIdAndDates(
        string $experienceId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $sql =
        <<<SQL
            SELECT 
                ra.date,
                ra.type,
                ra.stock,
                ra.component_golden_id AS componentGoldenId,
                ra.is_stop_sale AS isStopSale,
                fmc.duration,
                rp.price,
                last_bookable_date AS lastBookableDate,
                fmc.experience_golden_id AS experienceGoldenId,
                fmc.partner_golden_id AS partnerGoldenId,
                fmc.is_sellable AS isSellable,
                fmc.room_stock_type AS roomStockType
            FROM
                room_availability ra
            JOIN 
                (SELECT
                    experience_golden_id,
                    partner_golden_id,
                    duration,
                    is_sellable,
                    room_stock_type,
                    last_bookable_date,
                    component_uuid
                FROM
                    flat_manageable_component
                WHERE
                    experience_golden_id = :experienceId LIMIT 1) fmc ON fmc.component_uuid = ra.component_uuid
            LEFT JOIN room_price rp ON rp.component_uuid = ra.component_uuid AND rp.date = ra.date
            WHERE
                (ra.is_stop_sale = FALSE)
                AND (fmc.last_bookable_date IS NULL
                OR fmc.last_bookable_date >= ra.date)
                AND (ra.date BETWEEN :startDate AND :endDate)
            ;
        SQL;

        $statement = $this->getAvailabilityReadOnlyConnection()->prepare($sql);
        $statement->bindValue('experienceId', $experienceId);
        $statement->bindValue('startDate', $startDate->format('Y-m-d'));
        $statement->bindValue('endDate', $endDate->format('Y-m-d'));
        $statement->execute();

        return $statement->fetchAll();
    }
}
