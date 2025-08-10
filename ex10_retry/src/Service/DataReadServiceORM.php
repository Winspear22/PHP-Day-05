<?php

namespace App\Service;

use App\Entity\Data;
use App\Repository\DataRepository;

class DataReadServiceORM
{
    private $dataRepository;

    public function __construct(DataRepository $dataRepository)
    {
        $this->dataRepository = $dataRepository;
    }

    public function getDataById(int $id): ?Data
    {
        return $this->dataRepository->find($id);
    }

    public function getAllDatas(): array
    {
        return $this->dataRepository->findAll();
    }
}
?>