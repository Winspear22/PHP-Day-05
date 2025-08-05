<?php

namespace App\Service;

use App\Entity\Ex10OrmRecords;
use App\Repository\Ex10OrmRecordsRepository;

class DataReadServiceORM
{
    private Ex10OrmRecordsRepository $recordRepository;

    public function __construct(Ex10OrmRecordsRepository $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    public function getRecordById(int $id): ?Ex10OrmRecords
    {
        return $this->recordRepository->find($id);
    }

    public function getAllRecords(): array
    {
        return $this->recordRepository->findAll();
    }
}

?>