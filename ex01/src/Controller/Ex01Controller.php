<?php

namespace App\Controller;

use Throwable;
use App\Entity\User;
use App\Service\TableCreatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex01Controller extends AbstractController
{

    /**
     * @Route("/ex01", name="ex01_index")
     */
    public function index(): Response
    {
        return $this->render('ex01/index.html.twig', [
            'controller_name' => 'Ex01Controller',
        ]);
    }

    /**
     * @Route("/ex01/create_table", name="ex01_create_table", methods={"POST"})
     */
    public function createTable(EntityManagerInterface $em, TableCreatorService $tableCreator): Response
    {
        try
        {
            $message = $tableCreator->createTable($em, User::class, 'ex01_users');
            [$type, $text] = explode(':', $message, 2);
            $this->addFlash($type, $text);
            return $this->redirectToRoute('ex01_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error creating table: ' . $e->getMessage());
            return $this->redirectToRoute('ex01_index');
        }
    }
}
