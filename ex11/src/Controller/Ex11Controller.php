<?php

namespace App\Controller;

use Throwable;
use RuntimeException;
use Doctrine\DBAL\Connection;
use App\Service\TablesFillService;
use App\Service\ValidationService;
use App\Service\PersonsReadService;
use App\Service\TablesAlterService;
use App\Service\TablesCreatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

final class Ex11Controller extends AbstractController
{
    /**
     * @Route("/ex11", name="ex11_index")
     */
    public function index(
        PersonsReadService $personsReadService,
        Connection $connection, Request $request, ValidationService $validationService
    ): Response
    {
        $params = $validationService->validateQueryParams($request);
        extract($params); // $filter_name, $sort_by, $sort_dir, $limit, $flash_messages

        // Ajoute tous les messages flash retournés
        foreach ($params['messages'] as [$type, $msg])
            $this->addFlash($type, $msg);
        try
        {
            $data = $personsReadService->getPersonsGrouped($connection, $filter_name, $sort_by, $sort_dir);
        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', 'Error, in the index : ' . $e->getMessage());
            $data = [];
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, unexpected error while reading persons: ' . $e->getMessage());
            $data = [];
        }

        return $this->render('ex11/index.html.twig', [
            'data'        => $data,
            'filter_name' => $filter_name,
            'sort_by'     => $sort_by,
            'sort_dir'    => $sort_dir,
        ]);
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