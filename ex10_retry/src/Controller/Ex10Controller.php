<?php

namespace App\Controller;

use DateTime;
use Throwable;
use App\Entity\Data;
use RuntimeException;
use Doctrine\DBAL\Connection;
use App\Service\DataReadServiceORM;
use App\Service\DataReadServiceSQL;
use App\Service\DataDeleteServiceORM;
use App\Service\DataDeleteServiceSQL;
use App\Service\DataInsertServiceORM;
use App\Service\DataInsertServiceSQL;
use App\Service\TableCreatorServiceSQL;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex10Controller extends AbstractController
{
    /**
     * @Route("/ex10", name="ex10_index")
     */
    public function index(DataReadServiceSQL $dataReadServiceSQL, DataReadServiceORM $dataReadServiceORM, Connection $connection, TableCreatorServiceSQL $tableCreator): Response
    {
        $formSQL = $this->createDataForm();
        $formORM = $this->createDataForm();
        try
        {
            $tableCreator->createTable($connection, 'ex10_data_sql');

            $dataSQL = $dataReadServiceSQL->getAllDatas($connection, 'ex10_data_sql');
            $dataORM = $dataReadServiceORM->getAllDatas();

        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', "Error, database error: " . $e->getMessage());
            $dataSQL = [];
            $dataORM = [];
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', "Error, unexpected error: " . $e->getMessage());
            $dataSQL = [];
            $dataORM = [];
        }
        return $this->render('ex10/index.html.twig', [
            'formSQL' => $formSQL->createView(),
            'formORM' => $formORM->createView(),
            'datasSQL' => $dataSQL,
            'datasORM' => $dataORM
        ]);
    }


    /**
     * @Route("/ex10/insert_data_sql", name="ex10_insert_data_sql", methods={"POST"})
     */
    public function insertDataSQL(Request $request, DataInsertServiceSQL $dataInsertService, Connection $connection): Response
    {
        try
        {
            $form = $this->createDataForm();
            $form->handleRequest($request);
            $date = new DateTime();

            if ($form->isSubmitted() && $form->isValid())
            {
                $data = $form->get('comment')->getData();
                $result = $dataInsertService->insertData($connection, 'ex10_data_sql', $data, $date);
                [$type, $msg] = explode(':', $result, 2);
                $this->addFlash($type, $msg);
                return $this->redirectToRoute('ex10_index');
            }
            else
            {
                $this->addFlash('danger', 'Error, invalid form!');
                return $this->redirectToRoute('ex10_index');
            }
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, unexpected error while inserting data: ' . $e->getMessage());
            return $this->redirectToRoute('ex10_index');
        }
    }

    /**
     * @Route("/ex10/insert_data_orm", name="ex10_insert_data_orm", methods={"POST"})
     */
    public function insertDataORM(Request $request, DataInsertServiceORM $dataInsertService): Response
    {
        try
        {
            $form = $this->createDataForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $comment = $form->get('comment')->getData();
                $dataEntity = new Data();
                $dataEntity->setContent($comment);
                $dataEntity->setDate(new DateTime());

                $dataInsertService->insertData($dataEntity);
                $this->addFlash('success', 'Success! Data added successfully!');
                return $this->redirectToRoute('ex10_index');
            }
            else
            {
                $this->addFlash('danger', 'Error, invalid form!');
                return $this->redirectToRoute('ex10_index');
            }
        }
        catch(Throwable $e)
        {
            $this->addFlash('danger', 'Error, unexpected error while inserting data: ' . $e->getMessage());
            return $this->redirectToRoute('ex10_index');
        }
    }

    /**
     * @Route("/ex10/delete_data_sql/{id}", name="ex10_delete_data_sql", methods={"POST"})
     */
    public function deleteDataSQL(DataDeleteServiceSQL $dataDeleteService, Connection $connection, int $id): Response
    {
        try
        {
            $result = $dataDeleteService->deleteData($connection, 'ex10_data_sql', $id);
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
            return $this->redirectToRoute('ex10_index');

        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', "Unexpected error while deleting data: " . $e->getMessage());
            return $this->redirectToRoute('ex10_index');
        }
    }

    /**
     * @Route("/ex10/delete_data_orm/{id}", name="ex10_delete_data_orm", methods={"POST"})
     */
    public function delete(int $id, DataDeleteServiceORM $deleteService): Response
    {
        try
        {
            $success = $deleteService->deleteDataById($id);
            if ($success)
                $this->addFlash('success', "Success! Data was successfully deleted !");
            else
                $this->addFlash('danger', "Error, we could not find the data requested !");
            return $this->redirectToRoute('ex10_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, unexpected error: ' . $e->getMessage());
            return $this->redirectToRoute('ex10_index');
        }
    }

    private function createDataForm()
    {
        return $this->createFormBuilder()
            ->add('comment', TextType::class, [
                'label' => 'Comment',
                'constraints' => [
                    new NotBlank(['message' => 'Comment is required.']),
                    new Length(['max' => 255, 'maxMessage' => 'Maximum 255 characters allowed.']),
                ],
                'attr' => ['maxlength' => 255, 'placeholder' => 'Your comment']
            ])
            ->getForm();
    }

    /**
     * @Route("/ex10/import_file", name="ex10_import_file", methods={"POST"})
     */
    public function importFile(
        Connection $connection,
        TableCreatorServiceSQL $tableCreatorSQL,
        DataInsertServiceSQL $dataInsertServiceSQL,
        DataInsertServiceORM $dataInsertServiceORM
    ): Response
    {
        try
        {
            // Chemin vers le fichier à la racine du projet
            $filePath = $this->getParameter('kernel.project_dir') . '/text.txt';

            if (!file_exists($filePath))
            {
                $this->addFlash('danger', 'Error ! Could not find text.txt.');
                return $this->redirectToRoute('ex10_index');
            }

            // Lecture du fichier (une ligne = une donnée)
            $content = array_map(
            fn($line) => htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            );
            $date = new DateTime();

            // Création de la table SQL si besoin
            $tableCreatorSQL->createTable($connection, 'ex10_data_sql');

            // Insertion SQL
            foreach ($content as $line)
                $dataInsertServiceSQL->insertData($connection, 'ex10_data_sql', $line, $date);

            // Insertion ORM
            foreach ($content as $line)
            {
                $dataEntity = new Data();
                $dataEntity->setContent($line);
                $dataEntity->setDate($date);
                $dataInsertServiceORM->insertData($dataEntity);
            }

            $this->addFlash('success', 'Success! The SQL and ORM import was successful !');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error during the file import : ' . $e->getMessage());
        }

        return $this->redirectToRoute('ex10_index');
    }
        
}
?>