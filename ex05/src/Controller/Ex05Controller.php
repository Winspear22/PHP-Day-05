<?php

namespace App\Controller;

use Throwable;
use App\Entity\User;
use RuntimeException;
use App\Service\UserReadService;
use App\Service\UserDeleteService;
use App\Service\UserInsertService;
use Symfony\Component\Form\FormInterface;
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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex05Controller extends AbstractController
{
    /**
     * @Route("/ex05", name="ex05_index", methods={"GET", "POST"})
     */
    public function index(
        Request $request,
        UserReadService $userReadService,
        UserInsertService $userInsertService
    ): Response
    {
        $user = new User();
        $form = $this->createUserForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            try
            {
                $userInsertService->insertUser($user);
                $this->addFlash('success', 'Success! User added successfully!');
                return $this->redirectToRoute('ex05_index');
            }
            catch (UniqueConstraintViolationException $e)
            {
                $this->addFlash('danger', 'Error, email or username already in use !');
            }
            catch (Throwable $e)
            {
                $this->addFlash('danger', 'Error, unexpected error: ' . $e->getMessage());
            }
        }
        try
        {
            $users = $userReadService->getAllUsers();
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
        return $this->render('ex05/index.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
        ]);
    }

    /**
     * @Route("/ex05/delete/{id}", name="ex05_delete", methods={"POST"})
     */
    public function delete(int $id, UserDeleteService $deleteService): Response
    {
        try
        {
            $success = $deleteService->deleteUserById($id);
            if ($success)
                $this->addFlash('success', "Success! User was successfully deleted !");
            else
                $this->addFlash('danger', "Error, we could not find the user requested !");
            return $this->redirectToRoute('ex05_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, unexpected error: ' . $e->getMessage());
            return $this->redirectToRoute('ex05_index');
        }
    }


    private function createUserForm(User $user): FormInterface
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
}
