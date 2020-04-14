<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Room;
use App\Exception\Repository\RoomNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|Room find($id, $lockMode = null, $lockVersion = null)
 * @method null|Room findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function save(Room $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(Room $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): Room
    {
        $room = $this->find($uuid);

        if (null === $room) {
            throw new RoomNotFoundException();
        }

        return $room;
    }

    public function findOneByGoldenId(string $goldenId): Room
    {
        $room = $this->findOneBy(['goldenId' => $goldenId]);

        if (null === $room) {
            throw new RoomNotFoundException();
        }

        return $room;
    }
}
