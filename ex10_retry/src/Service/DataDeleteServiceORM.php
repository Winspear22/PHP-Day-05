<?php 

namespace App\Service;

use App\Repository\DataRepository;
use Doctrine\ORM\EntityManagerInterface;

class DataDeleteServiceORM
{
    private $em;
    private $repo;
    public function __construct(EntityManagerInterface $em, 
	DataRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }
    public function deleteDataById(int $id): bool
    {
        $data = $this->repo->find($id);
        if (!$data)
            return false;
        $this->em->remove($data);
        $this->em->flush();
        return true;
    }
}
?>