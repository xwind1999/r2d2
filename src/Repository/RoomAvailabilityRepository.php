<?php

declare(strict_types=1);

namespace App\Repository;

use App\Constants\DateTimeConstants;
use App\Constraint\BookingStatusConstraint;
use App\Constraint\RoomStockTypeConstraint;
use App\Entity\Booking;
use App\Entity\Component;
use App\Entity\RoomAvailability;
use App\Exception\Repository\RoomAvailabilityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\Statement;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findOneByComponentGoldenIdAndDate(
        string $componentGoldenId,
        \DateTimeInterface $date
    ): ?RoomAvailability {
        return $this->findOneBy([
            'componentGoldenId' => $componentGoldenId,
            'date' => $date,
        ]);
    }

    public function findAllByComponentGoldenIdAndDates(
        string $componentGoldenId,
        array $dates
    ): array {
        $qb = $this->createQueryBuilder('ra');
        $qb
            ->where('ra.componentGoldenId = :componentId')
            ->andWhere('ra.date IN (:dates)')
            ->setParameter('componentId', $componentGoldenId)
            ->setParameter('dates', $dates, Connection::PARAM_STR_ARRAY)
            ->indexBy('ra', 'ra.date')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findAvailableRoomsByBoxId(
        string $boxId,
        \DateTimeInterface $startDate
    ): array {
        return $this->findAvailableRoomsByBoxIdWithUpdatedQuery($boxId, $startDate);
    }

    public function findAvailableRoomsByBoxIdWithFirstQuery(
        string $boxId,
        \DateTimeInterface $startDate
    ): array {
        $sql = <<<SQL
SELECT
    t.experience_golden_id as experienceGoldenId, t.room_stock_type as roomStockType
    FROM (
        SELECT
            fmc.experience_golden_id, fmc.duration, fmc.last_bookable_date, fmc.room_stock_type, ra.date
        FROM
            room_availability ra
        JOIN
            flat_manageable_component fmc ON ra.component_uuid = fmc.component_uuid
        WHERE
          fmc.box_golden_id = :boxId
          AND ra.date BETWEEN :dateFrom AND DATE_ADD(:dateFrom, interval fmc.duration - 1 day)
          AND (fmc.last_bookable_date IS NULL OR ra.date <= (fmc.last_bookable_date - INTERVAL fmc.duration DAY))
          AND ra.is_stop_sale = false
          AND ((fmc.room_stock_type in (:stockType,:allotmentType) and ra.stock > 0) OR fmc.room_stock_type = :onRequestType)
        ) t
GROUP BY t.experience_golden_id, t.duration HAVING count(t.date) = t.duration;
SQL;

        $statement = $this->getAvailabilityReadOnlyConnection()->prepare($sql);
        $statement->bindValue('boxId', $boxId);
        $statement->bindValue('dateFrom', $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->bindValue('stockType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK);
        $statement->bindValue('onRequestType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST);
        $statement->bindValue('allotmentType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT);
        $statement->execute();

        return $statement->fetchAllAssociative();
    }

    public function findAvailableRoomsByBoxIdWithUpdatedQuery(
        string $boxId,
        \DateTimeInterface $startDate
    ): array {
        $sql = <<<SQL
(
    SELECT
        fmc.experience_golden_id as experienceGoldenId, fmc.room_stock_type as roomStockType, fmc.duration
    FROM
        flat_manageable_component fmc
    LEFT JOIN
        room_availability ra on fmc.component_uuid = ra.component_uuid AND ra.date BETWEEN :dateFrom AND DATE_ADD(:dateFrom, interval fmc.duration - 1 day)
    WHERE
        fmc.box_golden_id = :boxId AND
        fmc.room_stock_type = :onRequestType AND
        (ra.is_stop_sale is null OR ra.is_stop_sale = 0)
    GROUP BY
        fmc.experience_golden_id, ra.is_stop_sale
) UNION ALL (
    SELECT
        fmc.experience_golden_id as experienceGoldenId, fmc.room_stock_type  as roomStockType, fmc.duration
    FROM
        flat_manageable_component fmc
    JOIN
        room_availability ra on fmc.component_uuid = ra.component_uuid AND ra.date BETWEEN :dateFrom AND DATE_ADD(:dateFrom, interval fmc.duration - 1 day)
    WHERE
        fmc.box_golden_id = :boxId AND
        fmc.room_stock_type in (:stockType, :allotmentType) AND
        ra.uuid is not null AND
        ra.stock > 0 AND
        ra.is_stop_sale = 0
    GROUP BY
        fmc.experience_golden_id
    HAVING fmc.duration = count(ra.uuid)
)
SQL;

        $statement = $this->getAvailabilityReadOnlyConnection()->prepare($sql);
        $statement->bindValue('boxId', $boxId);
        $statement->bindValue('dateFrom', $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->bindValue('stockType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK);
        $statement->bindValue('onRequestType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST);
        $statement->bindValue('allotmentType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT);
        $statement->execute();

        $data = $statement->fetchAllAssociative();
        foreach ($data as &$item) {
            unset($item['duration']);
        }

        return $data;
    }

    /**
     * @todo - fix the processing flow of this method to exactly match the other ones in findAvailableRoomsByBoxId
     */
    public function findAvailableRoomsByBoxIdProcessingOnPHP(
        string $boxId,
        \DateTimeInterface $startDate
    ): array {
        $sql = <<<SQL
        SELECT
            fmc.experience_golden_id, fmc.duration, fmc.room_stock_type, ra.date
        FROM
            room_availability ra
        JOIN
            flat_manageable_component fmc ON ra.component_uuid = fmc.component_uuid
        WHERE
          fmc.box_golden_id = :boxId
          AND ra.date BETWEEN :dateFrom AND DATE_ADD(:dateFrom, interval fmc.duration - 1 day)
          AND (fmc.last_bookable_date IS NULL OR ra.date <= (fmc.last_bookable_date - INTERVAL fmc.duration DAY))
          AND ra.is_stop_sale = false
          AND ((fmc.room_stock_type in (:stockType,:allotmentType) and ra.stock > 0) OR fmc.room_stock_type = :onRequestType)
SQL;

        $statement = $this->getAvailabilityReadOnlyConnection()->prepare($sql);
        $statement->bindValue('boxId', $boxId);
        $statement->bindValue('dateFrom', $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->bindValue('stockType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK);
        $statement->bindValue('onRequestType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST);
        $statement->bindValue('allotmentType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT);
        $statement->execute();

        $st = $statement->fetchAllAssociative();

        $h = [];
        $z = [];
        foreach ($st as $entry) {
            $experienceId = $entry['experience_golden_id'];
            if (!isset($h[$experienceId])) {
                $h[$experienceId] = 0;
            }

            ++$h[$experienceId];

            if ($h[$experienceId] === (int) $entry['duration']) {
                $z[] = [
                    'experienceGoldenId' => $experienceId,
                    'roomStockType' => $entry['room_stock_type'],
                ];
            }
        }

        return $z;
    }

    public function findAvailableRoomsByMultipleExperienceIds(
        array $experienceGoldenIds,
        \DateTimeInterface $startDate
    ): array {
        $sql = <<<SQL
SELECT fmc.experience_golden_id, fmc.partner_golden_id, fmc.is_sellable, fmc.duration
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
        fmc.experience_golden_id in (:experienceGoldenIds) AND
        fmc.room_stock_type IN (:stockType,:allotmentType)
    ) fmc
    ON ra.component_uuid = fmc.component_uuid
WHERE
    ra.is_stop_sale = false AND
    (fmc.last_bookable_date IS NULL OR ra.date <= (fmc.last_bookable_date - INTERVAL fmc.duration DAY)) AND
    ra.stock > 0 AND
    ra.date BETWEEN :startDate AND DATE_ADD(:startDate, interval fmc.duration - 1 day)
GROUP BY experience_golden_id HAVING count(ra.date) = fmc.duration;
SQL;
        $values = [
            'experienceGoldenIds' => $experienceGoldenIds,
            'startDate' => $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
            'stockType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK,
            'allotmentType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT,
        ];
        $types = [
            'experienceGoldenIds' => Connection::PARAM_STR_ARRAY,
            'startDate' => \PDO::PARAM_STR,
            'stockType' => \PDO::PARAM_STR,
            'allotmentType' => \PDO::PARAM_STR,
        ];

        /** @var Statement $query */
        $query = $this->getAvailabilityReadOnlyConnection()->executeQuery($sql, $values, $types);

        return $query->fetchAllAssociative();
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
            ->setParameter('dateFrom', $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT))
            ->setParameter('dateTo', $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT))
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
            ->setParameter('oldAvailabilityDate', $cleanUpOlderThan->format(DateTimeConstants::DEFAULT_DATE_FORMAT))
            ->getQuery()
            ->execute()
        ;
    }

    public function cleanupInvalid(): int
    {
        $sql = <<<SQL
DELETE FROM
            room_availability ra
WHERE
      ra.stock = 0
      AND ra.is_stop_sale=0
LIMIT 500;
SQL;
        $statement = $this->_em->getConnection()->prepare($sql);
        $statement->execute();

        return $statement->rowCount();
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
        $statement->bindValue('dateFrom', $booking->startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->bindValue('dateTo', $booking->endDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->execute();

        return $statement->fetchAllAssociative();
    }

    public function updateStockForAvailability(
        string $componentGoldenId,
        \DateTime $date,
        int $decrement
    ): int {
        $sql = <<<SQL
    UPDATE 
        room_availability ra
        JOIN flat_manageable_component fmc ON fmc.component_uuid = ra.component_uuid
    SET
        stock = stock - :decrement,
        updated_at = now() 
    WHERE ra.component_golden_id = :componentGoldenId AND date = :date AND fmc.room_stock_type != :requestType
SQL;

        $params = [
            'componentGoldenId' => $componentGoldenId,
            'decrement' => $decrement,
            'requestType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST,
            'date' => $date->format(DateTimeConstants::DEFAULT_DATE_FORMAT),
        ];

        return $this->_em->getConnection()->executeStatement($sql, $params);
    }

    public function updateStocksForAvailability(
        string $componentGoldenId,
        array $dates,
        int $decrement
    ): void {
        $sql = <<<SQL
    UPDATE 
        room_availability ra
        JOIN flat_manageable_component fmc ON fmc.component_uuid = ra.component_uuid
    SET
        stock = stock - :decrement,
        updated_at = now() 
    WHERE ra.component_golden_id = :componentGoldenId 
    AND ra.date IN (:dates)
    AND fmc.room_stock_type != :requestType
SQL;

        $values = [
            'componentGoldenId' => $componentGoldenId,
            'decrement' => $decrement,
            'dates' => $dates,
            'requestType' => RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST,
        ];
        $types = [
            'componentGoldenId' => \PDO::PARAM_STR,
            'decrement' => \PDO::PARAM_INT,
            'dates' => Connection::PARAM_STR_ARRAY,
            'requestType' => \PDO::PARAM_STR,
        ];

        $this->getEntityManager()->getConnection()->executeQuery($sql, $values, $types);
    }

    public function findAvailableRoomsAndPricesByExperienceIdAndDates(
        string $experienceId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $sql =
        <<<SQL
            SELECT
                cal.date,
                COALESCE(IF(fmc.room_stock_type = :onRequestType,1,ra.stock), 0) as stock,
                COALESCE(ra.is_stop_sale, false) AS isStopSale,
                COALESCE(rp.price, 0) as price,
                fmc.duration,
                fmc.component_golden_id AS componentGoldenId,
                fmc.last_bookable_date AS lastBookableDate,
                fmc.experience_golden_id AS experienceGoldenId,
                fmc.partner_golden_id AS partnerGoldenId,
                fmc.is_sellable AS isSellable,
                fmc.room_stock_type AS roomStockType
            FROM
                (SELECT
                    experience_golden_id,
                    component_golden_id,
                    partner_golden_id,
                    duration,
                    is_sellable,
                    last_bookable_date,
                    component_uuid,
                    room_stock_type
                FROM
                    flat_manageable_component
                WHERE
                    experience_golden_id = :experienceId AND
                    room_stock_type in (:stockType,:allotmentType,:onRequestType)
                LIMIT 1) fmc
            JOIN calendar cal on cal.date BETWEEN :startDate AND :endDate AND (fmc.last_bookable_date IS NULL OR fmc.last_bookable_date >= cal.date)
            LEFT JOIN
                room_availability ra ON fmc.component_uuid = ra.component_uuid AND ra.date = cal.date
            LEFT JOIN
                room_price rp ON fmc.component_uuid = rp.component_uuid AND rp.date = cal.date;
        SQL;

        $statement = $this->getAvailabilityReadOnlyConnection()->prepare($sql);
        $statement->bindValue('experienceId', $experienceId);
        $statement->bindValue('startDate', $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->bindValue('endDate', $endDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->bindValue('stockType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK);
        $statement->bindValue('onRequestType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST);
        $statement->bindValue('allotmentType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT);
        $statement->execute();

        return $statement->fetchAllAssociative();
    }

    public function findBookingAvailabilityByExperienceAndDates(
        string $experienceGoldenId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $sql = <<<SQL
SELECT
   f.experience_golden_id as experienceGoldenId,
   r.component_golden_id as componentGoldenId,
   r.date AS date,
   coalesce(sub.used_stock, sub.used_stock, 0) as usedStock,
   IF(r.stock - (coalesce(sub.used_stock, sub.used_stock, 0)) > 0, r.stock - (coalesce(sub.used_stock, sub.used_stock, 0)), 0) AS realStock,
   r.stock
FROM
   room_availability r
    JOIN
      flat_manageable_component f 
      ON  f.component_uuid = r.component_uuid 
   LEFT JOIN
   (
        select
               bd.component_golden_id, bd.date, b.status, count(*) as used_stock
        FROM
            booking_date bd
            JOIN booking b on b.uuid = bd.booking_uuid
        WHERE
            b.expired_at > :dateNow
            AND bd.date BETWEEN :startDate and :endDate
        GROUP BY bd.component_golden_id, bd.date
       ) sub ON sub.component_golden_id = r.component_golden_id and sub.date = r.date AND sub.status = :status
 WHERE
   r.date BETWEEN :startDate AND :endDate
  AND f.experience_golden_id = :experienceGoldenId
  AND r.is_stop_sale = false AND
    (
        (f.room_stock_type = :onRequestType) OR (f.room_stock_type in (:stockType, :allotmentType) AND r.stock > 0)
    )
GROUP BY
   r.stock,
   r.date,
   f.experience_golden_id,
   f.component_golden_id
HAVING r.stock - usedStock > 0;
SQL;
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->bindValue('experienceGoldenId', $experienceGoldenId);
        $query->bindValue('startDate', $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        // Removing the last date as it should not count due to do not have night stay
        $query->bindValue('endDate', (
            new \DateTime($endDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT))
            )->modify('-1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );

        $dateNow = (new \DateTime('now'))->format(DateTimeConstants::DEFAULT_DATE_TIME_FORMAT);
        $query->bindValue('dateNow', $dateNow);
        $query->bindValue('status', BookingStatusConstraint::BOOKING_STATUS_CREATED);
        $query->bindValue('onRequestType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST);
        $query->bindValue('stockType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK);
        $query->bindValue('allotmentType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT);
        $query->execute();

        return $query->fetchAllAssociative();
    }

    public function findStopSaleOnRequestAvailabilityByExperienceAndDates(
        string $experienceGoldenId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $sql = <<<SQL
SELECT
   f.experience_golden_id as experienceGoldenId,
   r.component_golden_id as componentGoldenId,
   r.date AS date
FROM
    flat_manageable_component f
    LEFT JOIN
    room_availability r
    ON  f.component_uuid = r.component_uuid 
 WHERE
   r.date BETWEEN :startDate AND :endDate
  AND f.experience_golden_id = :experienceGoldenId
  AND f.room_stock_type = :onRequestType
  AND r.is_stop_sale = true
GROUP BY
   r.date,
   f.experience_golden_id,
   f.component_golden_id
SQL;
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->bindValue('experienceGoldenId', $experienceGoldenId);
        $query->bindValue('startDate', $startDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        // Removing the last date as it should not count due to do not have night stay
        $query->bindValue('endDate', (
        new \DateTime($endDate->format(DateTimeConstants::DEFAULT_DATE_FORMAT))
        )->modify('-1 day')->format(DateTimeConstants::DEFAULT_DATE_FORMAT)
        );
        $query->bindValue('onRequestType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ON_REQUEST);
        $query->execute();

        return $query->fetchAllAssociative();
    }

    public function deleteByComponentIdAndDateRange(
        Component $component,
        \DateTime $dateFrom,
        \DateTime $dateTo
    ): void {
        $sql = <<<SQL
            DELETE FROM
                room_availability ra
            WHERE
                ra.component_golden_id = :componentGoldenId
                AND ra.date >= :dateFrom AND ra.date < :dateTo
            ;
        SQL;

        $statement = $this->_em->getConnection()->prepare($sql);
        $statement->bindValue('componentGoldenId', $component->goldenId);
        $statement->bindValue('dateFrom', $dateFrom->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->bindValue('dateTo', $dateTo->format(DateTimeConstants::DEFAULT_DATE_FORMAT));
        $statement->execute();
    }
}
