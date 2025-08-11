<?php 
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Employee;

class EmployeeUpdateService
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function updateEmployee(Employee $employee): void
    {
        $this->em->flush();
	}
}

?>