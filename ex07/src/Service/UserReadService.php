<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserReadService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }
}
?>