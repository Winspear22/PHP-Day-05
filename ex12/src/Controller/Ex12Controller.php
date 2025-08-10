<?php

namespace App\Controller;

use Throwable;
use RuntimeException;
use App\Service\ValidationService;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TableDeleteAndFillService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex12Controller extends AbstractController
{
    /**
     * @Route("/ex12", name="ex12_index")
     */
    public function index(
        PersonRepository $personRepo,
        Request $request,
        ValidationService $validationService
    ): Response
    {
        $params = $validationService->validateQueryParams($request);
        extract($params); // $filter_name, $sort_by, $sort_dir, $limit, $flash_messages

        foreach ($params['flash_messages'] as [$type, $msg])
            $this->addFlash($type, $msg);

        try
        {
            $data = $personRepo->getPersonsGrouped($filter_name, $sort_by, $sort_dir);
        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', 'Error in index: ' . $e->getMessage());
            $data = [];
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Unexpected error: ' . $e->getMessage());
            $data = [];
        }

        return $this->render('ex12/index.html.twig', [
            'data'        => $data,
            'filter_name' => $filter_name,
            'sort_by'     => $sort_by,
            'sort_dir'    => $sort_dir,
        ]);
    }

    /**
     * @Route("/ex12/fill_tables", name="ex12_fill_all_tables", methods={"POST"})
     */
    public function fillAllTables(TableDeleteAndFillService $service): Response
    {
        try 
        {
            $service->clearAndFill();
            $this->addFlash('success', 'All ORM tables (persons, addresses, bank accounts) have been cleared and refilled successfully!');
        } 
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error while clearing and refilling the tables: ' . $e->getMessage());
        }

        return $this->redirectToRoute('ex12_index');
    }
}
