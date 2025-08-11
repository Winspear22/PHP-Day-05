<?php 

namespace App\Service;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;

class EmployeeDeleteService
{
    private $em;
    private $repo;
    public function __construct(EntityManagerInterface $em, 
	EmployeeRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }
    public function deleteEmployeeById(int $id): bool
    {
        $employee = $this->repo->find($id);
        if (!$employee)
            return false;
        $this->em->remove($employee);
        $this->em->flush();
        return true;
    }
}
?>