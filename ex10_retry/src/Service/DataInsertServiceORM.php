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
        //Je n'ai pas mis de transaction ici, car on suppose que l'insertion est simple. De toutes façons, le controller est protégé
        $this->em->persist($data);
        $this->em->flush();
    }
}
?>