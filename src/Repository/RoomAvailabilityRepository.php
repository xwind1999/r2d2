<?php

declare(strict_types=1);

namespace App\Repository;

use App\Constraint\RoomStockTypeConstraint;
use App\Entity\Component;
use App\Entity\RoomAvailability;
use App\Exception\Repository\RoomAvailabilityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
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
    private const ROOM_AVAILABILITY_MINIMAL_STOCK = 0;

    public function __construct(ManagerRegistry $registry)
    {
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
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $dateDiff = $dateTo->diff($dateFrom)->days ?: 0;
        $numberOfNights = $dateDiff + 1;

        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT e.golden_id as experienceGoldenId, group_concat(ra.type) as roomAvailabilities
            FROM room_availability ra
            JOIN component c on ra.component_uuid = c.uuid
            JOIN experience_component ec on c.uuid = ec.component_uuid
            JOIN experience e on e.uuid = ec.experience_uuid
            JOIN box_experience be on e.uuid = be.experience_uuid
            WHERE be.box_golden_id = :boxId
            AND ra.date BETWEEN :dateFrom AND :dateTo
            AND ra.is_stop_sale = false
            AND ((ra.type in (:stockType,:allotmentType)) and ra.stock > :minimalStock) OR (ra.type = :onRequestType)
            GROUP BY e.golden_id, c.duration
            HAVING (
                COUNT(ra.date) = (CASE WHEN :numberOfNights > c.duration THEN :numberOfNights ELSE c.duration END)
            )';

        $statement = $this->getEntityManager()->getConnection()->prepare($sql);
        $statement->bindValue('boxId', $boxId);
        $statement->bindValue('dateFrom', $dateFrom->format('Y-m-d'));
        $statement->bindValue('dateTo', $dateTo->format('Y-m-d'));
        $statement->bindValue('numberOfNights', $numberOfNights);
        $statement->bindValue('stockType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_STOCK);
        $statement->bindValue('onRequestType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ONREQUEST);
        $statement->bindValue('allotmentType', RoomStockTypeConstraint::ROOM_STOCK_TYPE_ALLOTMENT);
        $statement->bindValue('minimalStock', self::ROOM_AVAILABILITY_MINIMAL_STOCK);
        $statement->execute();

        return $statement->fetchAll();
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
        \DateTimeInterface $dateTo): array
    {
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
}
