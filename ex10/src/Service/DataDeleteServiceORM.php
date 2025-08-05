<?php 

namespace App\Service;

use App\Repository\Ex10OrmRecordsRepository;
use Doctrine\ORM\EntityManagerInterface;

class DataDeleteServiceORM
{
    private EntityManagerInterface $em;
    private Ex10OrmRecordsRepository $repo;

    public function __construct(EntityManagerInterface $em, Ex10OrmRecordsRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
    }

    public function deleteRecordById(int $id): bool
    {
        $record = $this->repo->find($id);
        if (!$record)
            return false;
        $this->em->remove($record);
        $this->em->flush();
        return true;
    }
}
?>