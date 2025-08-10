<?php

namespace App\Controller;

use DateTime;
use Throwable;
use RuntimeException;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use App\Service\UserReadService;
use App\Service\UserDeleteService;
use App\Service\TableCreatorService;
use App\Service\DataDeleteServiceSQL;
use App\Service\DataInsertServiceSQL;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex10Controller extends AbstractController
{
/**
     * @Route("/ex10", name="ex10_index")
     */
    public function index(UserReadService $userReadService, Connection $connection, TableCreatorService $tableCreator): Response
    {
        $form = $this->createDataForm();
        try
        {
            $tableCreator->createTable($connection, 'ex10_data_sql');
            $users = $userReadService->getAllUsers($connection, 'ex10_data_sql');
        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', "Error, database error: " . $e->getMessage());
            $users = [];
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', "Error, unexpected error: " . $e->getMessage());
            $users = [];
        }
        return $this->render('ex10/index.html.twig', [
            'form' => $form->createView(),
            'users' => $users
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
                $data = $form->getData();
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
            $result = $dataDeleteService->deleteData($connection, 'users_ex10', $id);
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
                    new Length(['max' => 255, 'maxMessage' => 'Maximum 25 characters allowed.']),
                ],
                'attr' => ['maxlength' => 255, 'placeholder' => 'Your comment']
            ])
            /*->add('birthdate', DateTimeType::class, [
                'label' => 'Birthdate',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Birthdate is required.']),
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'Birthdate cannot be in the future.'
                    ]),
                ],
            ])*/
            ->getForm();
    }
}
?>