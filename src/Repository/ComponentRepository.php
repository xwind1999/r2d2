<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Entity\BoxExperience;
use App\Entity\Component;
use App\Entity\Experience;
use App\Entity\ExperienceComponent;
use App\Exception\Repository\ComponentNotFoundException;
use App\Exception\Repository\ManageableProductNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

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

    /**
     * @throws ComponentNotFoundException
     *
     * @return array<Component>
     */
    public function findListByGoldenId(array $goldenIdList): array
    {
        $components = $this->findBy(['goldenId' => $goldenIdList]);

        if (empty($components)) {
            throw new ComponentNotFoundException();
        }

        return $components;
    }

    public function findOneByGoldenId(string $goldenId): Component
    {
        $component = $this->findOneBy(['goldenId' => $goldenId]);

        if (null === $component) {
            throw new ComponentNotFoundException();
        }

        return $component;
    }

    public function findDefaultRoomByExperience(Experience $experience): Component
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->join('c.experienceComponent', 'ec')
            ->where('c.isReservable = 1')
            ->andWhere('ec.experience = :experience')
            ->andWhere('ec.isEnabled = true')
            ->setParameter('experience', $experience->uuid->getBytes())
        ;

        $component = $qb->getQuery()->getOneOrNullResult();

        if (null === $component) {
            throw new ComponentNotFoundException();
        }

        return $component;
    }

    public function findRoomsByExperienceGoldenIdsList(array $expIds): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->addSelect('ec.experienceGoldenId')
            ->join('c.experienceComponent', 'ec')
            ->where('c.isReservable = 1')
            ->andWhere('ec.isEnabled = true')
            ->andWhere($qb->expr()->in('ec.experienceGoldenId', $expIds))
            ->indexBy('c', 'c.goldenId')
        ;

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @throws ManageableProductNotFoundException|NonUniqueResultException
     */
    public function findComponentWithManageableCriteria(Criteria $criteria): Component
    {
        $result = $this
            ->createQueryBuilderForCMHCriteria($criteria)
            ->groupBy('component.goldenId')
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $result) {
            throw new ManageableProductNotFoundException();
        }

        return $result;
    }

    /**
     * @throws ComponentNotFoundException|NonUniqueResultException
     */
    public function findComponentWithBoxExperienceAndRelationship(Criteria $criteria): Component
    {
        $result = $this->createQueryBuilderForCMHCriteria($criteria)->getQuery()->getOneOrNullResult();

        if (null === $result) {
            throw new ComponentNotFoundException();
        }

        return $result;
    }

    private function createQueryBuilderForCMHCriteria(Criteria $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('component');
        $qb = $this->getOrdinaryWhereConditionForCMHCriteria($qb);
        $qb->addCriteria($criteria);

        return $qb;
    }

    private function getOrdinaryWhereConditionForCMHCriteria(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->join(ExperienceComponent::class, 'experienceComponent')
            ->andWhere('experienceComponent.componentGoldenId = component.goldenId')

            ->join(Experience::class, 'experience')
            ->andWhere('experienceComponent.experienceGoldenId = experience.goldenId')

            ->join(BoxExperience::class, 'boxExperience')
            ->andWhere('experience.goldenId = boxExperience.experienceGoldenId')

            ->join(Box::class, 'box')
            ->andWhere('boxExperience.boxGoldenId = box.goldenId')
            ;
    }
}
