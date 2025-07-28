<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex03Controller extends AbstractController
{
    /**
     * @Route("/ex03", name="ex03_index")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $users = $this->safeGetAllUsers($em);

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            try 
			{
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Utilisateur créé avec succès.');
                return $this->redirectToRoute('ex03_index');
            } 
			catch (\Exception $e)
			{
                $this->addFlash('error', 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());
            }
        }

        return $this->render('index.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
        ]);
    }

    private function safeGetAllUsers(EntityManagerInterface $em): array
    {
        try 
		{
            return $em->getRepository(User::class)->findAll();
        } 
		catch (\Exception $e) 
		{
            $this->addFlash('error', "Erreur lors de l'accès à la base de données : " . $e->getMessage());
            return [];
        }
    }
}
