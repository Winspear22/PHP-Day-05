<?php

namespace App\Controller;

use Throwable;
use Doctrine\DBAL\Connection;
use App\Service\TableCreatorService;
use App\Service\DataInsertServiceSQL;
use App\Service\DataReadServiceSQL;
use App\Service\DataDeleteServiceSQL;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex10Controller extends AbstractController
{
    /**
     * @Route("/ex10", name="ex10_index")
     */
    public function index(
        DataReadServiceSQL $sqlReadService,
        Connection $connection,
        TableCreatorService $tableCreator
    ): Response
    {
        // Table creation (if not exists)
        $tableCreator->createTable($connection, 'ex10_sql_records');

        // Read all data
        try {
            $sqlRecords = $sqlReadService->getAllRecords($connection, 'ex10_sql_records');
        } catch (Throwable $e) {
            $this->addFlash('danger', 'Error, SQL read failure: ' . $e->getMessage());
            $sqlRecords = [];
        }

        return $this->render('ex10/index.html.twig', [
            'sqlRecords' => $sqlRecords,
        ]);
    }

    /**
     * @Route("/ex10/insert", name="ex10_insert", methods={"POST"})
     */
    public function insertRecord(Request $request, DataInsertServiceSQL $sqlInsertService, Connection $connection): Response
    {
        $tableName = 'ex10_sql_records';

        $data = $request->request->get('data');
        $date = new \DateTime();

        $msg = $sqlInsertService->insertRecord($connection, $tableName, $data, $date);
        [$type, $message] = explode(':', $msg, 2);

        // Reformate le message en respectant la consigne
        if ($type === 'danger' && strpos($message, 'Error,') !== 0)
            $message = 'Error, ' . ltrim($message);
        if ($type === 'success' && strpos($message, 'Success!') !== 0)
            $message = 'Success! ' . ltrim($message);
        if ($type === 'info' && strpos($message, 'Info,') !== 0)
            $message = 'Info, ' . ltrim($message);

        $this->addFlash($type, $message);

        return $this->redirectToRoute('ex10_index');
    }

    /**
     * @Route("/ex10/delete/{id}", name="ex10_delete", methods={"POST"})
     */
    public function deleteRecord(DataDeleteServiceSQL $deleteService, Connection $connection, int $id): Response
    {
        $result = $deleteService->deleteRecord($connection, 'ex10_sql_records', $id);
        [$type, $msg] = explode(':', $result, 2);

        if ($type === 'danger' && strpos($msg, 'Error,') !== 0)
            $msg = 'Error, ' . ltrim($msg);
        if ($type === 'success' && strpos($msg, 'Success!') !== 0)
            $msg = 'Success! ' . ltrim($msg);
        if ($type === 'info' && strpos($msg, 'Info,') !== 0)
            $msg = 'Info, ' . ltrim($msg);

        $this->addFlash($type, $msg);
        return $this->redirectToRoute('ex10_index');
    }
}
