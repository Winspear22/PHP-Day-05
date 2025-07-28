<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Address;
use App\Entity\BankAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex09Controller extends AbstractController
{
    /**
     * @Route("/ex09", name="ex09_index")
     */
    public function index(EntityManagerInterface $em): Response
    {
        try 
        {
            $people = $em->getRepository(Person::class)->findAll();
        } 
        catch (\Exception $e) 
        {
            $this->addFlash('notice', "Erreur d'accès à la base de données : " . $e->getMessage());
            $people = [];
        }
        return $this->render('index.html.twig', ['people' => $people]);
    }


    /**
     * @Route("/ex09/add", name="ex09_add", methods={"POST"})
     */
    public function addPerson(EntityManagerInterface $em): Response
    {
        try 
        {
            $nbPeople = $em->getRepository(Person::class)->count([]);
        } 
        catch (\Exception $e) 
        {
            return $this->redirectToRoute('ex09_index');
        }        
        if ($nbPeople >= 10) 
        {
            $this->addFlash('notice', "❌ Impossible d'ajouter plus de 10 personnes !");
            return $this->redirectToRoute('ex09_index');
        }

        $statuses = ['single', 'married', 'widower', 'divorced', 'separated', 'engaged'];
        $person = new Person();
        $person->setUsername('user'.rand(100,999));
        $person->setName('Nom'.rand(100,999));
        $person->setEmail('user'.rand(100,999).'@mail.com');
        $person->setEnable(true);
        $person->setBirthdate(new \DateTime('1990-01-01'));
        $person->setMaritalStatus($statuses[array_rand($statuses)]);

        try 
        {
            $em->persist($person);
            $em->flush();
            $this->addFlash('notice', "✅ Personne ajoutée, id = ".$person->getId());
        } 
        catch (\Exception $e) 
        {
            $this->addFlash('notice', "Erreur lors de l'ajout : ".$e->getMessage());
        }
        return $this->redirectToRoute('ex09_index');
    }

    /**
     * @Route("/ex09/add-address/{id}", name="ex09_add_address", methods={"POST"})
     */
    public function addAddress($id, EntityManagerInterface $em): Response
    {
        try
        {
            $person = $em->getRepository(Person::class)->find($id);
        }
        catch (\Exception $e)
        {
            return $this->redirectToRoute('ex09_index');
        }
        if (!$person) 
        {
            $this->addFlash('notice', 'Person not found');
            return $this->redirectToRoute('ex09_index');
        }
        if (count($person->getAddresses()) >= 3) {
            $this->addFlash('notice', "❌ Maximum 3 adresses par personne !");
            return $this->redirectToRoute('ex09_index');
        }

        $address = new Address();
        $address->setStreet('12 rue '.rand(1,50));
        $address->setCity('Paris');
        $address->setCountry('France');
        $address->setPerson($person);

        try {
            $em->persist($address);
            $em->flush();
            $this->addFlash('notice', '✅ Adresse ajoutée à la personne '.$person->getId());
        } catch (\Exception $e) {
            $this->addFlash('notice', "Erreur : ".$e->getMessage());
        }
        return $this->redirectToRoute('ex09_index');
    }

    /**
     * @Route("/ex09/add-bank-account/{id}", name="ex09_add_bank_account", methods={"POST"})
     */
    public function addBankAccount($id, EntityManagerInterface $em): Response
    {
        try
        {
            $person = $em->getRepository(Person::class)->find($id);
        }
        catch (\Exception $e)
        {
            return $this->redirectToRoute('ex09_index');
        }
        if (!$person) 
        {
            $this->addFlash('notice', 'Person not found');
            return $this->redirectToRoute('ex09_index');
        }
        if ($person->getBankAccount()) 
        {
            $this->addFlash('notice', "❌ Cette personne a déjà un compte bancaire !");
            return $this->redirectToRoute('ex09_index');
        }

        $bank = new BankAccount();
        $bank->setIban('FR76'.rand(100000000000000, 999999999999999));
        $bank->setBankName('Banque'.rand(1,5));
        $person->setBankAccount($bank);

        try 
        {
            $em->persist($bank);
            $em->persist($person);
            $em->flush();
            $this->addFlash('notice', '✅ Compte bancaire ajouté à la personne '.$person->getId());
        } 
        catch (\Exception $e) 
        {
            $this->addFlash('notice', "Erreur : ".$e->getMessage());
        }
        return $this->redirectToRoute('ex09_index');
    }

    /**
     * @Route("/ex09/delete/{id}", name="ex09_delete", methods={"POST"})
     */
    public function deletePerson($id, EntityManagerInterface $em): Response
    {
		try
        {
            $person = $em->getRepository(Person::class)->find($id);
        }
        catch (\Exception $e)
        {
            return $this->redirectToRoute('ex09_index');
        }
        if (!$person) 
		{
            $this->addFlash('notice', "Personne inexistante");
            return $this->redirectToRoute('ex09_index');
        }
        try 
		{
            $em->remove($person);
            $em->flush();
            $this->addFlash('notice', "✅ Personne supprimée (id={$id})");
        } 
		catch (\Exception $e) 
		{
            $this->addFlash('notice', "Erreur lors de la suppression : ".$e->getMessage());
        }
        return $this->redirectToRoute('ex09_index');
    }
}
