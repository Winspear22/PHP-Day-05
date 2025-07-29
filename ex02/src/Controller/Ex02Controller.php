<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use App\Service\UserReadService;
use App\Service\UserInsertService;
use App\Service\TableCreatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
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
        /*$result = $tableCreator->createTable($connection, 'users_ex02');
        if ($result)
        {
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
        }*/
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
            ->add('username', TextType::class, [
                'label' => 'Username',
                'constraints' => [
                    new NotBlank(['message' => 'Username is required.']),
                    new Length(['max' => 25, 'maxMessage' => 'Maximum 25 characters allowed.']),
                ],
                'attr' => ['maxlength' => 25, 'placeholder' => 'Your username']
            ])
            ->add('name', TextType::class, [
                'label' => 'Full name',
                'constraints' => [
                    new NotBlank(['message' => 'Name is required.']),
                    new Length(['max' => 25, 'maxMessage' => 'Maximum 25 characters allowed.']),
                ],
                'attr' => ['maxlength' => 25, 'placeholder' => 'Your full name']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Email is required.']),
                    new Email(['message' => 'Invalid email address.']),
                    new Length(['max' => 255, 'maxMessage' => 'Maximum 255 characters allowed.']),
                ],
                'attr' => ['maxlength' => 255, 'placeholder' => 'email@example.com']
            ])
            ->add('enable', CheckboxType::class, [
                'label' => 'Enabled?',
                'required' => false,
            ])
            ->add('birthdate', DateTimeType::class, [
                'label' => 'Birthdate',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Birthdate is required.']),
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'Birthdate cannot be in the future.'
                    ]),
                ],
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Address',
                'constraints' => [
                    new NotBlank(['message' => 'Address is required.']),
                ],
                'attr' => ['rows' => 3, 'placeholder' => 'Your full address']
            ])
            ->getForm();
    }

}
