<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Exception\Repository\BoxNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Box find($id, $lockMode = null, $lockVersion = null)
 * @method null|Box findOneBy(array $criteria, array $orderBy = null)
 * @method Box[]    findAll()
 * @method Box[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Box::class);
    }

    public function save(Box $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(Box $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): Box
    {
        $box = $this->find($uuid);

        if (null === $box) {
            throw new BoxNotFoundException();
        }

        return $box;
    }

    public function findOneByGoldenId(string $goldenId): Box
    {
        $box = $this->findOneBy(['goldenId' => $goldenId]);

        if (null === $box) {
            throw new BoxNotFoundException();
        }

        return $box;
    }
}
