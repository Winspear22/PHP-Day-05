<?php

namespace App\Controller;

use DateTime;
use Throwable;
use RuntimeException;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use App\Service\UserReadService;
use App\Service\UserDeleteService;
use App\Service\UserInsertService;
use App\Service\UserUpdateService;
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

final class Ex06Controller extends AbstractController
{
    /**
     * @Route("/ex06", name="ex06_index")
     */
    public function index(UserReadService $userReadService, Connection $connection, TableCreatorService $tableCreator): Response
    {
        $form = $this->createUserForm();
        try
        {
            $tableCreator->createTable($connection, 'users_ex06');
            $users = $userReadService->getAllUsers($connection, 'users_ex06');
        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', "Database error: " . $e->getMessage());
            $users = [];
        }
        return $this->render('ex06/index.html.twig', [
            'form' => $form->createView(),
            'users' => $users
        ]);
    }


    /**
     * @Route("/ex06/insert_user", name="ex06_insert_user", methods={"POST"})
     */
    public function insertUser(Request $request, UserInsertService $userInsertService, Connection $connection): Response
    {
        try
        {
            $form = $this->createUserForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $data = $form->getData();
                $result = $userInsertService->insertUser($connection, 'users_ex06', $data);
                [$type, $msg] = explode(':', $result, 2);
                $this->addFlash($type, $msg);
                return $this->redirectToRoute('ex06_index');
            }
            else
            {
                $this->addFlash('danger', 'Error, invalid form!');
                return $this->redirectToRoute('ex06_index');
            }
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Unexpected error while inserting user: ' . $e->getMessage());
            return $this->redirectToRoute('ex06_index');
        }
    }

    /**
     * @Route("/ex06/read_user", name="ex06_read_user", methods={"GET"})
     */
    public function readUser(Connection $connection, UserReadService $userReadService): Response
    {
        try
        {
            $users = $userReadService->getAllUsers($connection, 'users_ex06');
        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', $e->getMessage());
            $users = [];
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Unexpected error while reading users: ' . $e->getMessage());
            $users = [];
        }
        return $this->render('ex06/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/ex06/delete_user/{id}", name="ex06_delete_user", methods={"POST"})
     */
    public function deleteUser(UserDeleteService $userDeleteService, Connection $connection, int $id): Response
    {
        try
        {
            $result = $userDeleteService->deleteUser($connection, 'users_ex06', $id);
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
            return $this->redirectToRoute('ex06_index');

        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', "Unexpected error while deleting user: " . $e->getMessage());
            return $this->redirectToRoute('ex06_index');
        }
    }

    /**
     * @Route("/ex06/update_user/{id}", name="ex06_update_user", methods={"GET", "POST"})
    */
    public function updateUser(
        int $id,
        Request $request,
        UserReadService $userReadService,
        UserUpdateService $userUpdateService,
        Connection $connection
    ): Response
    {
        try
        {
            $user = $userReadService->getUserById($connection, 'users_ex06', $id);
            if (!$user)
            {
                $this->addFlash('danger', "User $id does not exist.");
                return $this->redirectToRoute('ex06_index');
            }
            if (isset($user['enable']))
                $user['enable'] = (bool)$user['enable'];
            if (isset($user['birthdate']) && !($user['birthdate'] instanceof DateTimeInterface))
                $user['birthdate'] = new DateTime($user['birthdate']);
            $form = $this->updateUserForm($user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $data = $form->getData();
                $result = $userUpdateService->updateUser($connection, 'users_ex06', $id, $data);
                [$type, $msg] = explode(':', $result, 2);
                $this->addFlash($type, $msg);
                return $this->redirectToRoute('ex06_index');
            }

            return $this->render('ex06/edit.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
            ]);
        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', "Error, the form creation had a problem : " . $e->getMessage());
            return $this->redirectToRoute('ex06_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Unexpected error while updating user: ' . $e->getMessage());
            return $this->redirectToRoute('ex06_index');
        }
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
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Address cannot be longer than 1000 characters.',
                ]),
                ],
                'attr' => ['rows' => 3, 'placeholder' => 'Your full address', 'maxlength' => 1000]
            ])
            ->getForm();
    }

    private function updateUserForm(array $user)
    {
        return $this->createFormBuilder($user)
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
                // Le champ sera coché ou non selon $user['enable']
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
                // Symfony convertit le champ automatiquement si la valeur du tableau $user est correcte (format 'Y-m-d H:i:s')
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Address',
                'constraints' => [
                    new NotBlank(['message' => 'Address is required.']),
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Address cannot be longer than 1000 characters.',
                    ]),
                ],
                'attr' => ['rows' => 3, 'placeholder' => 'Your full address', 'maxlength' => 1000]
            ])
            ->getForm();
    }


}
