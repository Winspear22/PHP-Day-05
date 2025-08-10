<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Data;

class DataInsertServiceORM
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function insertData(Data $data): void
    {
        $this->em->persist($data);
        $this->em->flush();
    }
}
?>