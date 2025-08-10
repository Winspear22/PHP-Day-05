<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * Retourne les personnes avec leurs adresses et comptes bancaires,
     * filtrées/triées comme dans l'ex11.
     */
    public function getPersonsGrouped(
        string $filterName = '',
        string $sortBy = 'name',
        string $sortDir = 'asc'
    ): array
    {
        // Mapping des champs autorisés
        $allowedSorts = ['name', 'email', 'birthdate'];
        $allowedDir = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSorts, true))
            $sortBy = 'name';

        if (!in_array($sortDir, $allowedDir, true))
            $sortDir = 'asc';

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.addresses', 'a')->addSelect('a')
            ->leftJoin('p.bankAccount', 'b')->addSelect('b');

        if ($filterName)
        {
            $qb->andWhere('p.name LIKE :filter')
            ->setParameter('filter', "%$filterName%");
        }

        $qb->orderBy("p.$sortBy", $sortDir)
        ->addOrderBy('a.id', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
