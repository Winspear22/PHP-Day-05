<?php

namespace App\Service;

use App\Repository\UserRepository;
class UserReadService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }
}
?>