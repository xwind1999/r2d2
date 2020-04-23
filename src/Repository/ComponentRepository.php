<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Component;
use App\Exception\Repository\ComponentNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method null|Component find($id, $lockMode = null, $lockVersion = null)
 * @method null|Component findOneBy(array $criteria, array $orderBy = null)
 * @method Component[]    findAll()
 * @method Component[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComponentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Component::class);
    }

    public function save(Component $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete(Component $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function findOne(string $uuid): Component
    {
        $component = $this->find($uuid);

        if (null === $component) {
            throw new ComponentNotFoundException();
        }

        return $component;
    }

    public function findOneByGoldenId(string $goldenId): Component
    {
        $component = $this->findOneBy(['goldenId' => $goldenId]);

        if (null === $component) {
            throw new ComponentNotFoundException();
        }

        return $component;
    }
}
