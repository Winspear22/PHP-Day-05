<?php

namespace App\Controller;

use Throwable;
use Doctrine\DBAL\Connection;
use App\Service\TablesFillService;
use App\Service\TablesAlterService;
use App\Service\TablesCreatorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex11Controller extends AbstractController
{
    /**
     * @Route("/ex11", name="ex11_index")
     */
    public function index(): Response
    {  
        return $this->render('ex11/index.html.twig');
    }

    /**
     * @Route("/ex11/create_all_tables", name="ex11_create_all_tables", methods={"POST"})
     */
    public function createAllTables(TablesCreatorService $tablesCreator, TablesAlterService $tablesAlter, Connection $connection): Response
    {
        try
        {
            $messages = $tablesCreator->createAllTables($connection);
            foreach ($tablesAlter->alterAllTables($connection) as $msg)
                $messages[] = $msg;
            foreach ($messages as $message)
            {
                $parts = explode(':', $message, 2);
                if (count($parts) === 2)
                {
                    [$type, $msg] = $parts;
                    $this->addFlash($type, $msg);
                }
                else
                    $this->addFlash('danger', $message);
            }
            return $this->redirectToRoute('ex11_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
            return $this->redirectToRoute('ex11_index');
        }
    }

    /**
     * @Route("/ex11/fill_tables", name="ex11_fill_all_tables", methods={"POST"})
     */
    public function fillAllTables(
    TablesFillService $fillService,
    Connection $connection): Response
    {
        try
        {
            $messages = $fillService->fillAll($connection, 10); // par exemple, 10 personnes
            foreach ($messages as $message)
            {
                $parts = explode(':', $message, 2);
                if (count($parts) === 2)
                {
                    [$type, $msg] = $parts;
                    $this->addFlash($type, $msg);
                }
                else
                    $this->addFlash('danger', $message);
            }
            return $this->redirectToRoute('ex11_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
            return $this->redirectToRoute('ex11_index');
        }
    }
}