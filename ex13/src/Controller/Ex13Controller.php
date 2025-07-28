<?php

namespace App\Controller;

use Exception;
use App\Entity\Employee;
use App\Enum\EmployeePosition;
use App\Form\EmployeeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Ex13Controller extends AbstractController
{
    /**
     * @Route("/ex13", name="ex13_index")
     */
    public function index(EntityManagerInterface $em): Response
    {
		try
		{
			$employees = $em->getRepository(Employee::class)->findAll();
		}
		catch(Exception $e)
		{
			$this->addFlash('error', 'Erreur lors de la récupération des employés : ' . $e->getMessage());
			$employees = [];
		}
        return $this->render('index.html.twig', [
            'employees' => $employees,
        ]);
    }

/**
 * @Route("/ex13/create", name="ex13_create")
 */
public function create(Request $request, EntityManagerInterface $em): Response
{
    $employee = new Employee();
    $form = $this->createForm(EmployeeFormType::class, $employee);
    $form->handleRequest($request);

    if ($form->isSubmitted()) 
	{
        if (!$form->isValid()) 
		{
            foreach ($form->getErrors(true) as $error)
                $this->addFlash('error', $error->getMessage());
        } 
		else
		{
            $error = $this->validateBusinessRules($employee, $em);
            if ($error)
                $this->addFlash('error', $error);
            else 
			{
                try
				{
                    $em->persist($employee);
                    $em->flush();
                    $this->addFlash('success', 'Employé créé avec succès.');
                    return $this->redirectToRoute('ex13_index');
                }
				catch (Exception $e)
				{
                    $this->addFlash('error', 'Erreur lors de la création de l\'employé : ' . $e->getMessage());
                }
            }
        }
    }

    return $this->render('create.html.twig', [
        'form' => $form->createView(),
    ]);
}


/**
 * @Route("/ex13/update/{id}", name="ex13_update")
 */
public function update(Request $request, EntityManagerInterface $em, int $id): Response
{
    if (!is_numeric($id)) 
	{
        $this->addFlash('error', "ID invalide.");
        return $this->redirectToRoute('ex13_index');
    }

    try 
	{
        $employee = $em->getRepository(Employee::class)->find($id);
    } 
	catch (\Exception $e) 
	{
        $this->addFlash('error', 'Erreur lors de la récupération de l\'employé : ' . $e->getMessage());
        return $this->redirectToRoute('ex13_index');
    }

    if (!$employee)
	{
        $this->addFlash('error', "Employé non trouvé.");
        return $this->redirectToRoute('ex13_index');
    }

    $form = $this->createForm(EmployeeFormType::class, $employee);
    $form->handleRequest($request);

    if ($form->isSubmitted())
	{
        if (!$form->isValid())
		{
            foreach ($form->getErrors(true) as $error)
                $this->addFlash('error', $error->getMessage());
        }
		else
		{
            $error = $this->validateBusinessRules($employee, $em, $employee->getId());
            if ($error)
                $this->addFlash('error', $error);
            else
			{
                try
				{
                    $em->flush();
                    $this->addFlash('success', 'Employé mis à jour.');
                    return $this->redirectToRoute('ex13_index');
                }
				catch (Exception $e)
				{
                    $this->addFlash('error', 'Erreur lors de la mise à jour de l\'employé : ' . $e->getMessage());
                }
            }
        }
    }

    return $this->render('update.html.twig', [
        'form' => $form->createView(),
        'employee' => $employee,
    ]);
}


	/**
	 * @Route("/ex13/delete/{id}", name="ex13_delete", methods={"POST"})
	 */
	public function delete(EntityManagerInterface $em, int $id)
	{
		try
		{
			$employee = $em->getRepository(Employee::class)->find($id);
		}
		catch (\Exception $e)
		{
			$this->addFlash('error', 'Erreur lors de la récupération de l\'employé : ' . $e->getMessage());
			return $this->redirectToRoute('ex13_index');
		}
		if (!$employee) 
		{
			$this->addFlash('error', 'Employé non trouvé.');
			return $this->redirectToRoute('ex13_index');
		}

		$error = $this->canDeleteEmployee($employee, $em);
		if ($error)
		{
			$this->addFlash('error', $error);
			return $this->redirectToRoute('ex13_index');
		}

		try
		{
			$em->remove($employee);
			$em->flush();
			$this->addFlash('success', "Suppression effectuée avec succès.");
		} 
		catch (\Exception $e)
		{
			$this->addFlash('error', "Erreur lors de la suppression : " . $e->getMessage());
		}
		return $this->redirectToRoute('ex13_index');
	}


    /**
     * Validation métier partagée pour CREATE/UPDATE
     * @param Employee $employee
     * @param EntityManagerInterface $em
     * @param int|null $currentId
     * @return string|null
     */
    private function validateBusinessRules(Employee $employee, EntityManagerInterface $em, ?int $currentId = null): ?string
    {
		try
		{
			$repo = $em->getRepository(Employee::class);
		}
		catch (\Exception $e)
		{
			return "Erreur lors de l'accès au repository d'Employee : " . $e->getMessage();
		}
        // Premier employé = CEO obligatoire
        if ($repo->count([]) === 0 && $employee->getPosition()?->value !== 'ceo')
		{
            return "Le premier employé doit être CEO.";
        }

        // Unicité CEO
        if ($employee->getPosition()?->value === 'ceo')
		{
            $exists = $repo->findOneBy(['position' => EmployeePosition::Ceo]);
            if ($exists && ($currentId === null || $exists->getId() !== $currentId))
                return "Il existe déjà un CEO.";
            if ($employee->getManager() !== null) 
                return "Le CEO ne peut pas avoir de manager.";
        }

        // Unicité COO
        if ($employee->getPosition()?->value === 'coo')
		{
            $exists = $repo->findOneBy(['position' => EmployeePosition::Coo]);
            if ($exists && ($currentId === null || $exists->getId() !== $currentId))
                return "Il existe déjà un COO.";
            $ceo = $repo->findOneBy(['position' => EmployeePosition::Ceo]);
            if (!$ceo) 
                return "Impossible de créer un COO sans CEO.";
            if (!$employee->getManager() || $employee->getManager()->getPosition()?->value !== 'ceo')
                return "Le COO doit avoir le CEO comme manager.";
        }

        // Un employé ne peut pas être son propre manager
        if ($employee->getManager() && $employee->getManager() === $employee)
            return "Un employé ne peut pas être son propre manager.";

        // Managers et hiérarchie spécifique
        if ($employee->getPosition()?->value === 'manager')
		{
            if (!$employee->getManager() || !in_array($employee->getManager()->getPosition()?->value, ['ceo', 'coo']))
                return "Un Manager doit être rattaché à un CEO ou COO.";
        }
        if (in_array($employee->getPosition()?->value, ['account_manager', 'qa_manager', 'dev_manager']))
		{
            if (!$employee->getManager() || !in_array($employee->getManager()->getPosition()?->value, ['manager', 'ceo', 'coo']))
                return "Ce poste doit avoir comme manager un manager (ou CEO/COO).";
        }
        if (in_array($employee->getPosition()?->value, ['backend_dev', 'frontend_dev', 'qa_tester']))
		{
            if (!$employee->getManager() ||
                !in_array($employee->getManager()->getPosition()?->value, [
                    'manager', 'dev_manager', 'qa_manager', 'ceo', 'coo'
                ]))
                return "Ce poste doit avoir comme manager un manager, dev_manager, qa_manager, CEO ou COO.";
        }

        // Email unique, hors modification de soi-même
        $exists = $repo->findOneBy(['email' => $employee->getEmail()]);
        if ($exists && ($currentId === null || $exists->getId() !== $currentId))
            return "Cet email est déjà utilisé.";

        return null;
    }

	private function canDeleteEmployee(Employee $employee, EntityManagerInterface $em): ?string
{
    // Empêche de supprimer le CEO sauf s’il est seul
    if ($employee->getPosition()?->value === 'ceo') 
	{
		try
		{
			$total = $em->getRepository(Employee::class)->count([]);
			if ($total > 1)
				return "Impossible de supprimer le CEO s'il y a d'autres employés.";
		}
		catch (\Exception $e)
		{
			return "Erreur lors de l'accès au repository d'Employee : " . $e->getMessage();
		}
    }
    // Empêche de supprimer le COO s’il manage des employés
    if ($employee->getPosition()?->value === 'coo' && count($employee->getEmployees()) > 0)
        return "Impossible de supprimer le COO tant qu'il manage des employés.";
    // Empêche de supprimer un manager qui a encore des subordonnés
    if (count($employee->getEmployees()) > 0)
        return "Impossible de supprimer un manager qui manage encore des employés.";

    return null; // Autorisé
}

}
