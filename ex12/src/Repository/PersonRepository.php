<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

public function findWithFilters(?string $filterName, string $sortBy = 'name', string $sortDir = 'asc')
{
    $qb = $this->createQueryBuilder('p')
        ->leftJoin('p.addresses', 'a')
        ->leftJoin('p.bank_account', 'b')
        ->addSelect('a', 'b');

    if ($filterName) 
    {
        $qb->andWhere('p.name LIKE :filterName')
        ->setParameter('filterName', '%' . $filterName . '%');
    }

    $allowedSorts = ['name', 'email', 'birthdate', 'city'];
    $allowedDir = ['asc', 'desc'];
    if (!in_array($sortBy, $allowedSorts)) $sortBy = 'name';
    if (!in_array($sortDir, $allowedDir)) $sortDir = 'asc';

    // Gestion du tri selon la colonne choisie
    if ($sortBy === 'city') {
        // Ici tu tries sur la première adresse associée à la personne
        $qb->orderBy('a.city', $sortDir);
    } else {
        $qb->orderBy('p.' . $sortBy, $sortDir);
    }

    return $qb->getQuery()->getResult();
}

}
