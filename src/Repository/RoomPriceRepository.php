<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RoomPrice;
use App\Exception\Repository\RoomPriceNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RoomPrice[]    findAll()
 * @method RoomPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|RoomPrice find($id, $lockMode = null, $lockVersion = null)
 * @method null|RoomPrice findOneBy(array $criteria, array $orderBy = null)
 */
class RoomPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomPrice::class);
    }

    public function save(RoomPrice $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(RoomPrice $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): RoomPrice
    {
        $roomPrice = $this->find($uuid);

        if (null === $roomPrice) {
            throw new RoomPriceNotFoundException();
        }

        return $roomPrice;
    }
}
