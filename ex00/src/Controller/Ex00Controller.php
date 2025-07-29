<?php

namespace App\Controller;

use App\Service\TableCreatorService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex00Controller extends AbstractController
{
    
    /**
     * @Route("/ex00", name="ex00_index")
     */
    public function index(): Response
    {
        return $this->render('ex00/index.html.twig', [
            'controller_name' => 'Ex00Controller',
        ]);
    }

    /**
     * @Route("/ex00/create_table", name="ex00_create_table")
     */
    public function createTable(TableCreatorService $tableCreator, Connection $connection): Response
    {
        $result = $tableCreator->createTable($connection, 'users');
        [$type, $msg] = explode(':', $result, 2);
        $this->addFlash($type, $msg);
        return $this->redirectToRoute('ex00_index');
    }
}
