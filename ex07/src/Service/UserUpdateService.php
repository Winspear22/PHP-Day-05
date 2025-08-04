<?php 
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class UserUpdateService
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function updateUser(User $user): void
    {
        $this->em->flush();
	}
}

?>