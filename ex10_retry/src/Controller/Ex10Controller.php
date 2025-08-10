<?php

namespace App\Controller;

use DateTime;
use Throwable;
use RuntimeException;
use Doctrine\DBAL\Connection;
use App\Service\DataReadServiceSQL;
use App\Service\DataDeleteServiceSQL;
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
    public function index(DataReadServiceSQL $dataReadService, Connection $connection, TableCreatorServiceSQL $tableCreator): Response
    {
        $form = $this->createDataForm();
        try
        {
            $tableCreator->createTable($connection, 'ex10_data_sql');
            $data = $dataReadService->getAllDatas($connection, 'ex10_data_sql');
        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', "Error, database error: " . $e->getMessage());
            $data = [];
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', "Error, unexpected error: " . $e->getMessage());
            $data = [];
        }
        return $this->render('ex10/index.html.twig', [
            'form' => $form->createView(),
            'datas' => $data
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
            $this->addFlash('danger', "Unexpected error while deleting user: " . $e->getMessage());
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
}
?>