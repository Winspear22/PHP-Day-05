<?php

namespace App\Controller;

use Throwable;
use Doctrine\DBAL\Connection;
use App\Entity\Ex10OrmRecords;
use App\Service\ImportService;
use App\Service\ImportServiceEx10;
use App\Service\DataReadServiceORM;
use App\Service\DataReadServiceSQL;
use App\Service\TableCreatorService;
use App\Service\DataDeleteServiceORM;
use App\Service\DataDeleteServiceSQL;
use App\Service\DataInsertServiceORM;
use App\Service\DataInsertServiceSQL;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex10Controller extends AbstractController
{
    /**
     * @Route("/ex10", name="ex10_index", methods={"GET"})
     */
    public function index(
        DataReadServiceSQL $sqlReadService,
        DataReadServiceORM $ormReadService,
        Connection $connection,
        TableCreatorService $tableCreator
    ): Response
    {
        // Création table SQL si absente
        $tableCreator->createTable($connection, 'ex10_sql_records');

        // Lecture SQL
        try { $sqlRecords = $sqlReadService->getAllRecords($connection, 'ex10_sql_records'); }
        catch (Throwable $e) {
            $this->addFlash('danger', 'Error, SQL read failure: ' . $e->getMessage());
            $sqlRecords = [];
        }
        // Lecture ORM
        try { $ormRecords = $ormReadService->getAllRecords(); }
        catch (Throwable $e) {
            $this->addFlash('danger', 'Error, ORM read failure: ' . $e->getMessage());
            $ormRecords = [];
        }

        return $this->render('ex10/index.html.twig', [
            'sqlRecords' => $sqlRecords,
            'ormRecords' => $ormRecords,
        ]);
    }

    // ----------- SQL INSERT (Formulaire GET + traitement POST) --------------

    /**
     * @Route("/ex10/insert_sql", name="ex10_insert_sql", methods={"GET"})
     */
    public function showSqlInsertForm(): Response
    {
        $form = $this->createFormBuilder()
            ->add('data', TextareaType::class, [
                'label' => 'Data (SQL)',
                'attr' => ['maxlength' => 255, 'rows' => 2, 'placeholder' => 'Enter SQL data…'],
                'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Insert'])
            ->getForm();
        return $this->render('ex10/insert_sql.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ex10/insert_sql", name="ex10_insert_sql_post", methods={"POST"})
     */
    public function handleSqlInsert(
        Request $request,
        DataInsertServiceSQL $sqlInsertService,
        Connection $connection
    ): Response
    {
        $form = $this->createFormBuilder()->add('data', TextareaType::class)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('data')->getData();
            $date = new \DateTime();
            $msg = $sqlInsertService->insertRecord($connection, 'ex10_sql_records', $data, $date);
            [$type, $message] = explode(':', $msg, 2);
            if ($type === 'danger' && strpos($message, 'Error,') !== 0) $message = 'Error, ' . ltrim($message);
            if ($type === 'success' && strpos($message, 'Success!') !== 0) $message = 'Success! ' . ltrim($message);
            $this->addFlash($type, $message);
            return $this->redirectToRoute('ex10_index');
        }
        return $this->redirectToRoute('ex10_insert_sql');
    }

    /**
     * @Route("/ex10/delete_sql/{id}", name="ex10_delete_sql", methods={"POST"})
     */
    public function deleteSqlRecord(DataDeleteServiceSQL $deleteService, Connection $connection, int $id): Response
    {
        $result = $deleteService->deleteRecord($connection, 'ex10_sql_records', $id);
        [$type, $msg] = explode(':', $result, 2);
        if ($type === 'danger' && strpos($msg, 'Error,') !== 0) $msg = 'Error, ' . ltrim($msg);
        if ($type === 'success' && strpos($msg, 'Success!') !== 0) $msg = 'Success! ' . ltrim($msg);
        $this->addFlash($type, $msg);
        return $this->redirectToRoute('ex10_index');
    }

    // -------------- ORM INSERT (Formulaire GET + traitement POST) -------------

    /**
     * @Route("/ex10/insert_orm", name="ex10_insert_orm", methods={"GET"})
     */
    public function showOrmInsertForm(): Response
    {
        $form = $this->createFormBuilder()
            ->add('data', TextareaType::class, [
                'label' => 'Data (ORM)',
                'attr' => ['maxlength' => 255, 'rows' => 2, 'placeholder' => 'Enter ORM data…'],
                'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Insert'])
            ->getForm();
        return $this->render('ex10/insert_orm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ex10/insert_orm", name="ex10_insert_orm_post", methods={"POST"})
     */
    public function handleOrmInsert(Request $request, DataInsertServiceORM $ormInsertService): Response
    {
        $form = $this->createFormBuilder()->add('data', TextareaType::class)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('data')->getData();
            $record = new Ex10OrmRecords();
            $record->setData($data);
            $record->setDate(new \DateTime());
            try {
                $ormInsertService->insertRecord($record);
                $this->addFlash('success', 'Success! Data inserted into ORM table.');
            } catch (Throwable $e) {
                $this->addFlash('danger', 'Error, unexpected ORM insert error: ' . $e->getMessage());
            }
            return $this->redirectToRoute('ex10_index');
        }
        return $this->redirectToRoute('ex10_insert_orm');
    }

    /**
     * @Route("/ex10/delete_orm/{id}", name="ex10_delete_orm", methods={"POST"})
     */
    public function deleteOrmRecord(DataDeleteServiceORM $deleteService, int $id): Response
    {
        try {
            $ok = $deleteService->deleteRecordById($id);
            $msg = $ok
                ? "Success! Data $id deleted from ORM table."
                : "Error, data $id does not exist in ORM table.";
            $this->addFlash($ok ? 'success' : 'danger', $msg);
        } catch (Throwable $e) {
            $this->addFlash('danger', 'Error, unexpected ORM delete error: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex10_index');
    }

    // --------------- IMPORT (POST) --------------------------------------------

    /**
     * @Route("/ex10/import", name="ex10_import", methods={"POST"})
     */
    public function importRecords(
        ImportService $importService,
        Connection $connection,
        DataInsertServiceSQL $sqlInsertService,
        DataInsertServiceORM $ormInsertService
    ): Response
    {
        try {
            $filename = $this->getParameter('kernel.project_dir') . '/data.txt';
            $result = $importService->importFromFile($filename, $connection, $sqlInsertService, $ormInsertService);

            $this->addFlash('success', "Success! {$result['success_sql']} rows imported into SQL, {$result['success_orm']} rows into ORM.");
            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $err) {
                    $this->addFlash('danger', $err);
                }
            }
        } catch (Throwable $e) {
            $this->addFlash('danger', 'Error, import error: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex10_index');
    }
}
