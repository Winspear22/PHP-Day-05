<?php

namespace App\Controller;

use Throwable;
use RuntimeException;
use App\Enum\HoursEnum;
use App\Entity\Employee;
use App\Enum\PositionEnum;
use App\Service\EmployeeReadService;
use App\Repository\EmployeeRepository;
use App\Service\EmployeeDeleteService;
use App\Service\EmployeeInsertService;
use App\Service\EmployeeUpdateService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex13Controller extends AbstractController
{
    /**
     * @Route("/ex13", name="ex13_index", methods={"GET", "POST"})
     */
    public function index(
        Request $request,
        EmployeeReadService $employeeReadService,
        EmployeeInsertService $employeeInsertService): Response
    {

        $employee = new Employee();
        $form = $this->createEmployeeForm($employee);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            try
            {
                $employeeInsertService->insertEmployee($employee);
                $this->addFlash('success', 'Success! Employee added successfully!');
                return $this->redirectToRoute('ex13_index');
            }
            catch (UniqueConstraintViolationException $e)
            {
                $this->addFlash('danger', 'Error, email already in use !');
            }
            catch (Throwable $e)
            {
                $this->addFlash('danger', 'Error, unexpected error: ' . $e->getMessage());
            }
        }
        try
        {
            $employees = $employeeReadService->getAllEmployees();
        }
        catch (RuntimeException $e)
        {
            $this->addFlash('danger', "Error, database error: " . $e->getMessage());
            $employees = [];
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', "Error, unexpected error: " . $e->getMessage());
            $employees = [];
        }
        return $this->render('ex13/index.html.twig', [
            'form' => $form->createView(),
            'employees' => $employees,
        ]);
    }

    /**
     * @Route("/ex13/delete/{id}", name="ex13_delete", methods={"POST"})
     */
    public function delete(int $id, EmployeeDeleteService $deleteService): Response
    {
        try
        {
            $success = $deleteService->deleteEmployeeById($id);
            if ($success)
                $this->addFlash('success', "Success! Employee was successfully deleted !");
            else
                $this->addFlash('danger', "Error, we could not find the employee requested !");
            return $this->redirectToRoute('ex13_index');
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error, unexpected error: ' . $e->getMessage());
            return $this->redirectToRoute('ex13_index');
        }
    }

    /**
     * @Route("/ex13/update/{id}", name="ex13_update", methods={"GET", "POST"})
     */
    public function update(int $id, Request $request, EmployeeReadService $employeeReadService, EmployeeUpdateService $employeeUpdateService): Response
    {
        $employee = $employeeReadService->getEmployeeById($id);
        if (!$employee) {
            $this->addFlash('danger', "Employee #$id does not exist.");
            return $this->redirectToRoute('ex13_index');
        }

        $form = $this->updateEmployeeForm($employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $employeeUpdateService->updateEmployee($employee);
                $this->addFlash('success', "Employee #$id updated successfully!");
                return $this->redirectToRoute('ex13_index');
            } catch (Throwable $e) {
                $this->addFlash('danger', 'Error updating employee: ' . $e->getMessage());
            }
        }

        return $this->render('ex13/update.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
        ]);
    }

    private function updateEmployeeForm(Employee $employee): FormInterface
    {
        return $this->createFormBuilder($employee)
            ->add('firstname', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'First name is required.']),
                    new Length(['max' => 60, 'maxMessage' => 'Maximum 60 characters allowed.']),
                ],
                'attr' => ['maxlength' => 60, 'placeholder' => 'First name']
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Last name is required.']),
                    new Length(['max' => 60, 'maxMessage' => 'Maximum 60 characters allowed.']),
                ],
                'attr' => ['maxlength' => 60, 'placeholder' => 'Last name']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Email is required.']),
                    new Email(['message' => 'Invalid email address.']),
                    new Length(['max' => 100, 'maxMessage' => 'Maximum 100 characters allowed.']),
                ],
                'attr' => ['maxlength' => 100, 'placeholder' => 'email@example.com']
            ])
            ->add('birthdate', DateTimeType::class, [
                'label' => 'Birthdate',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Birthdate is required.']),
                    new LessThanOrEqual(['value' => 'today', 'message' => 'Birthdate cannot be in the future.']),
                ],
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
            ])
            ->add('employed_since', DateTimeType::class, [
                'label' => 'Employed Since',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Employment start date is required.']),
                ],
            ])
            ->add('employed_until', DateTimeType::class, [
                'label' => 'Employed Until',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('hours', ChoiceType::class, [
                'label' => 'Hours',
                'choices' => [
                    '8 hours' => HoursEnum::EIGHT,
                    '6 hours' => HoursEnum::SIX,
                    '4 hours' => HoursEnum::FOUR,
                ],
                'placeholder' => 'Select hours'
            ])
            ->add('salary', IntegerType::class, [
                'label' => 'Salary',
                'constraints' => [
                    new NotBlank(['message' => 'Salary is required.']),
                ],
            ])
            ->add('manager', EntityType::class, [
                'class' => Employee::class,
                'query_builder' => function (EmployeeRepository $er) use ($employee) {
                    return $er->createQueryBuilder('e')
                        ->where('e != :current')
                        ->setParameter('current', $employee);
                },
                'choice_label' => fn(Employee $e) => $e->getFirstname() . ' ' . $e->getLastname(),
                'placeholder' => 'Select a manager',
                'required' => false,
            ])
            ->add('position', ChoiceType::class, [
                'label' => 'Position',
                'choices' => [
                    'Manager' => PositionEnum::MANAGER,
                    'Account Manager' => PositionEnum::ACCOUNT_MANAGER,
                    'QA Manager' => PositionEnum::QA_MANAGER,
                    'Dev Manager' => PositionEnum::DEV_MANAGER,
                    'CEO' => PositionEnum::CEO,
                    'COO' => PositionEnum::COO,
                    'Backend Dev' => PositionEnum::BACKEND_DEV,
                    'Frontend Dev' => PositionEnum::FRONTEND_DEV,
                    'QA Tester' => PositionEnum::QA_TESTER,
                ],
                'placeholder' => 'Select position'
            ])
            ->getForm();
    }
    private function createEmployeeForm(Employee $employee): FormInterface
    {
        return $this->createFormBuilder($employee)
            ->add('firstname', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'First name is required.']),
                    new Length(['max' => 60, 'maxMessage' => 'Maximum 60 characters allowed.']),
                ],
                'attr' => ['maxlength' => 60, 'placeholder' => 'First name']
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Last name is required.']),
                    new Length(['max' => 60, 'maxMessage' => 'Maximum 60 characters allowed.']),
                ],
                'attr' => ['maxlength' => 60, 'placeholder' => 'Last name']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Email is required.']),
                    new Email(['message' => 'Invalid email address.']),
                    new Length(['max' => 100, 'maxMessage' => 'Maximum 100 characters allowed.']),
                ],
                'attr' => ['maxlength' => 100, 'placeholder' => 'email@example.com']
            ])
            ->add('birthdate', DateTimeType::class, [
                'label' => 'Birthdate',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Birthdate is required.']),
                    new LessThanOrEqual(['value' => 'today', 'message' => 'Birthdate cannot be in the future.']),
                ],
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
            ])
            ->add('employed_since', DateTimeType::class, [
                'label' => 'Employed Since',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Employment start date is required.']),
                ],
            ])
            ->add('employed_until', DateTimeType::class, [
                'label' => 'Employed Until',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('hours', ChoiceType::class, [
                'label' => 'Hours',
                'choices' => [
                    '8 hours' => HoursEnum::EIGHT,
                    '6 hours' => HoursEnum::SIX,
                    '4 hours' => HoursEnum::FOUR,
                ],
                'placeholder' => 'Select hours'
            ])
            ->add('salary', IntegerType::class, [
                'label' => 'Salary',
                'constraints' => [
                    new NotBlank(['message' => 'Salary is required.']),
                ],
            ])
            ->add('manager', EntityType::class, [
                'class' => Employee::class,
                'query_builder' => function (EmployeeRepository $er) use ($employee) {
                    return $er->createQueryBuilder('e')
                        ->where('e != :current')
                        ->setParameter('current', $employee);
                },
                'choice_label' => fn(Employee $e) => $e->getFirstname() . ' ' . $e->getLastname(),
                'placeholder' => 'Select a manager',
                'required' => false,
            ])

            ->add('position', ChoiceType::class, [
                'label' => 'Position',
                'choices' => [
                    'Manager' => PositionEnum::MANAGER,
                    'Account Manager' => PositionEnum::ACCOUNT_MANAGER,
                    'QA Manager' => PositionEnum::QA_MANAGER,
                    'Dev Manager' => PositionEnum::DEV_MANAGER,
                    'CEO' => PositionEnum::CEO,
                    'COO' => PositionEnum::COO,
                    'Backend Dev' => PositionEnum::BACKEND_DEV,
                    'Frontend Dev' => PositionEnum::FRONTEND_DEV,
                    'QA Tester' => PositionEnum::QA_TESTER,
                ],
                'placeholder' => 'Select position'
            ])
            ->getForm();
    }
}
