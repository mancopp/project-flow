<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByProjectAndCriteria(Project $project, $sort, $direction, $search, $statusId)
    {
        $qb = $this->createQueryBuilder('t')
                   ->andWhere('t.project = :project')
                   ->setParameter('project', $project);

        if ($search) {
            $qb->andWhere('t.name LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($statusId) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $statusId);
        }

        if ($sort && in_array($sort, ['name', 'status'])) {
            $qb->orderBy('t.' . $sort, $direction === 'desc' ? 'DESC' : 'ASC');
        } else {
            $qb->orderBy('t.name', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}