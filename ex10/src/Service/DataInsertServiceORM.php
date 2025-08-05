<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ex10OrmRecords;

class DataInsertServiceORM
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function insertRecord(Ex10OrmRecords $record): void
    {
        $this->em->persist($record);
        $this->em->flush();
    }
}

?>