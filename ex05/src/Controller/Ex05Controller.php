<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex05Controller extends AbstractController
{
    /**
     * @Route("/ex05", name="ex05_index")
     */
    public function index(EntityManagerInterface $em): Response
    {
        try 
        {
            $users = $em->getRepository(User::class)->findAll();
        } 
        catch (\Exception $e) 
        {
            $this->addFlash('error', "Erreur d'accès à la base de données : " . $e->getMessage());
            $users = [];
        }
        return $this->render('index.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/ex05/create", name="ex05_create")
     */
    public function createRandomUsers(EntityManagerInterface $em)
    {
        $i = 0;
        try 
        {
            $checkUsersExistence = $em->getRepository(User::class)->findAll();
            if (count($checkUsersExistence) > 0)
            {
                foreach ($checkUsersExistence as $users)
                    $em->remove($users);
                $em->flush();
            }
            while ($i < 10)
            {
                $user = new User();
                $user->setUsername('user' . $i);
                $user->setName('Nom' . $i);
                $user->setEmail('user' . $i . '@mail.com');
                $user->setEnable(rand(0, 1) === 1);
                $user->setBirthdate(new \DateTime('-' . rand(18, 40) . ' years'));
                $user->setAddress('Adresse ' . $i . ' avenue Testville');
                $em->persist($user);
                $i++;
            }
            $em->flush();
            $this->addFlash('success', '10 users créés avec succès.');
        } 
        catch (\Exception $e) 
        {
            $this->addFlash('error', 'Erreur lors de la création des users : ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex05_index');
    }


	/**
	 * @Route("/ex05/delete/{id}", name="ex05_delete", methods={"POST"})
	 */
	public function delete(EntityManagerInterface $em, $id)
	{
        try
        {
            $user = $em->getRepository(User::class)->find($id);
            if ($user)
            {
                    $em->remove($user);
                    $em->flush();
                    $this->addFlash('success', "Suppression effectuée avec succès.");
            }
            else
                $this->addFlash('error', 'Le user avec l\'id ' . $id . ' non trouvé.');
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', 'Erreur lors de la suppression du user : ' . $id . ' ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex05_index');
	}
}
