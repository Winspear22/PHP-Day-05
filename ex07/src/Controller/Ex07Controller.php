<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex07Controller extends AbstractController
{
    /**
     * @Route("/ex07", name="ex07_index")
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
     * @Route("/ex07/create", name="ex07_create")
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
            $this->addFlash('success', '10 utilisateurs créés avec succès.');
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', 'Erreur lors de la création des utilisateurs : ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex07_index');
    }

    /**
     * @Route("/ex07/update/{id}", name="ex07_update")
     */
    public function updateUser(Request $request, EntityManagerInterface $em, int $id): Response
    {
        try
        {
            $user = $em->getRepository(User::class)->find($id);
            if (!$user)
            {
                $this->addFlash('error', 'Utilisateur avec l\'id ' . $id . ' non trouvé.');
                return $this->redirectToRoute('ex07_index');
            }

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                try
                {
                    $em->flush();
                    $this->addFlash('success', 'Utilisateur modifié avec succès.');
                    return $this->redirectToRoute('ex07_index');
                }
                catch (\Exception $e)
                {
                    $this->addFlash('error', 'Erreur lors de la mise à jour de l\'utilisateur : ' . $e->getMessage());
                }
            }
        }
        catch (\Exception $e)
        {
            $this->addFlash('error', "Erreur d'accès à la base de données : " . $e->getMessage());
            return $this->redirectToRoute('ex07_index');
        }

        return $this->render('update.html.twig', [
            'form' => isset($form) ? $form->createView() : null,
            'user' => isset($user) ? $user : null
        ]);
    }

}
