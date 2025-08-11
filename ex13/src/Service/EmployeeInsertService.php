<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Employee;

class EmployeeInsertService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function insertEmployee(Employee $employee): void
    {
        $this->em->persist($employee);
        $this->em->flush();
    }
}
?>