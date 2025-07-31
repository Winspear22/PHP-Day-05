<?php 

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserDeleteService
{
    private $em;
    private $repo;
    public function __construct(EntityManagerInterface $em, 
	UserRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }
    public function deleteUserById(int $id): bool
    {
        $user = $this->repo->find($id);
        if (!$user)
            return false;
        $this->em->remove($user);
        $this->em->flush();
        return true;
    }
}
?>