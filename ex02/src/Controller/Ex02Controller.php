<?php

namespace App\Controller;

use App\Service\TableCreatorService;
use Doctrine\DBAL\Connection;
use App\Service\UserReadService;
use App\Service\UserInsertService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex02Controller extends AbstractController
{
    /**
     * @Route("/ex02", name="ex02_index")
     */
    public function index(UserReadService $userReadService, Connection $connection, TableCreatorService $tableCreator): Response
    {
        $form = $this->createUserForm();
        $tableCreator->createTable($connection, 'users_ex02');
        $users = $userReadService->getAllUsers($connection, 'users_ex02');

        return $this->render('ex02/index.html.twig', [
            'form' => $form->createView(),
            'users' => $users
        ]);
    }

    /**
     * @Route("/ex02/insert_user", name="ex02_insert_user", methods={"POST"})
     */
    public function insertUser(Request $request, UserInsertService $userInsertService, Connection $connection): Response
    {
        $form = $this->createUserForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $result = $userInsertService->insertUser($connection, 'users_ex02', $data);
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
        }
        else
        {
            $this->addFlash('danger', 'Error, invalid form!');
        }
        return $this->redirectToRoute('ex02_index');
    }

    /**
     * @Route("/ex02/read_user", name="ex02_read_user", methods={"GET"})
     */
    public function readUser(Connection $connection, UserReadService $userReadService): Response
    {
        try
        {
            $users = $userReadService->getAllUsers($connection, 'users_ex02');
        }
        catch (\RuntimeException $e)
        {
            $this->addFlash('danger', $e->getMessage());
            $users = [];
        }
        return $this->render('ex02/index.html.twig', [
            'users' => $users
        ]);
    }


    private function createUserForm()
    {
        return $this->createFormBuilder()
            ->add('username')
            ->add('name')
            ->add('email')
            ->add('enable')
            ->add('birthdate')
            ->add('address')
            ->getForm();
    }
}
