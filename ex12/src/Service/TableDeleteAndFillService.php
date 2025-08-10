<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\Address;
use App\Entity\BankAccount;
use Doctrine\ORM\EntityManagerInterface;

class TableDeleteAndFillService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function clearAndFill(int $nbPersons = 10): void
    {
        // 1. Suppression des données
        foreach ($this->em->getRepository(Address::class)->findAll() as $address)
            $this->em->remove($address);
        foreach ($this->em->getRepository(BankAccount::class)->findAll() as $account)
            $this->em->remove($account);
        foreach ($this->em->getRepository(Person::class)->findAll() as $person)
            $this->em->remove($person);
        $this->em->flush();

        // 2. Création des nouvelles données
        for ($i = 1; $i <= $nbPersons; $i++)
			{
            $person = new Person();
            $person->setUsername('user' . uniqid());
            $person->setName('Name ' . $i);
            $person->setEmail('user' . $i . '@test.com');
            $person->setEnabled((bool) rand(0, 1));
            $person->setBirthdate((new \DateTime())->modify('-' . rand(20, 40) . ' years'));

            // Compte bancaire
            $bankAccount = new BankAccount();
            $bankAccount->setIban('FR' . rand(1000000000, 9999999999) . rand(1000, 9999));
            $bankAccount->setBankName('Bank #' . rand(1, 5));
            $bankAccount->setPerson($person);

            $person->setBankAccount($bankAccount);

            // Adresses
            $nbAddresses = rand(1, 2);
            for ($j = 1; $j <= $nbAddresses; $j++)
				{
                $address = new Address();
                $address->setAddress('Fictive address #' . rand(1000, 9999));
                $address->setPerson($person);
                $person->addAddress($address);
                $this->em->persist($address);
            }

            $this->em->persist($bankAccount);
            $this->em->persist($person);
        }

        // Sauvegarde
        $this->em->flush();
    }
}
