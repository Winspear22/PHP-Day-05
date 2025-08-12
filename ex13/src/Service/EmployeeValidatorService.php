<?php

namespace App\Service;

use App\Entity\Employee;
use App\Enum\PositionEnum;
use App\Repository\EmployeeRepository;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EmployeeValidatorService
{
    private EmployeeRepository $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    public function validateCEO(Employee $employee, ?ExecutionContextInterface $context = null): array
    {
        $errors = [];

        // 1. Le premier employé doit être le CEO
        if ($employee->getId() === null && $this->employeeRepository->count([]) === 0) {
            if ($employee->getPosition() !== PositionEnum::CEO) {
                $errors[] = 'The first employee must be the CEO.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        // 2. Un CEO ne peut pas avoir de manager
        if ($employee->getPosition() === PositionEnum::CEO && $employee->getManager() !== null) {
            $errors[] = 'The CEO cannot have a manager.';
            $this->addViolation($context, 'manager', end($errors));
        }

        // 3. Un seul CEO dans l’entreprise
        if ($employee->getPosition() === PositionEnum::CEO) {
            $existingCEO = $this->employeeRepository->findOneBy(['position' => PositionEnum::CEO]);
            if ($existingCEO && $existingCEO->getId() !== $employee->getId()) {
                $errors[] = 'There can only be one CEO in the company.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        // 4. Bloquer le changement de rôle si c'est l'actuel CEO
        $currentCEO = $this->employeeRepository->findOneBy(['position' => PositionEnum::CEO]);
        if ($currentCEO && $currentCEO->getId() === $employee->getId()) {
            if ($employee->getPosition() !== PositionEnum::CEO) {
                $errors[] = 'The CEO position cannot be changed.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        // ========================
        //       COO RULES
        // ========================

        // 1. Le deuxième employé doit être COO
        if ($employee->getId() === null && $this->employeeRepository->count([]) === 1) {
            if ($employee->getPosition() !== PositionEnum::COO) {
                $errors[] = 'The second employee must be the COO.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        // 2. Un seul COO dans l’entreprise
        if ($employee->getPosition() === PositionEnum::COO) {
            $existingCOO = $this->employeeRepository->findOneBy(['position' => PositionEnum::COO]);
            if ($existingCOO && $existingCOO->getId() !== $employee->getId()) {
                $errors[] = 'There can only be one COO in the company.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        // 3. Bloquer le changement de rôle si c'est l'actuel COO
        $currentCOO = $this->employeeRepository->findOneBy(['position' => PositionEnum::COO]);
        if ($currentCOO && $currentCOO->getId() === $employee->getId()) {
            if ($employee->getPosition() !== PositionEnum::COO) {
                $errors[] = 'The COO position cannot be changed.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        // 4. Le COO doit avoir le CEO comme manager
        if ($employee->getPosition() === PositionEnum::COO) {
            if (!$employee->getManager() || $employee->getManager()->getPosition() !== PositionEnum::CEO) {
                $errors[] = 'The COO must have the CEO as manager.';
                $this->addViolation($context, 'manager', end($errors));
            }
        }

        return $errors;
    }

    /**
     * Vérifie si le CEO peut être supprimé.
     */
    public function canDeleteCEO(Employee $employee): bool
    {
        $currentCEO = $this->employeeRepository->findOneBy(['position' => PositionEnum::CEO]);

        if ($currentCEO && $currentCEO->getId() === $employee->getId()) {
            $totalEmployees = $this->employeeRepository->count([]);
            return $totalEmployees <= 1; // Suppression possible seulement s'il est le dernier employé
        }

        return true; // Pas le CEO, suppression autorisée
    }

    /**
     * Vérifie si le COO peut être supprimé.
     */
    public function canDeleteCOO(Employee $employee): bool
    {
        if ($employee->getPosition() !== PositionEnum::COO) {
            return true;
        }

        // On vérifie s'il manage quelqu'un
        $managedEmployees = $this->employeeRepository->findBy(['manager' => $employee]);
        return count($managedEmployees) === 0; // Impossible de supprimer si le COO manage des employés
    }

    private function addViolation(?ExecutionContextInterface $context, string $field, string $message): void
    {
        if ($context) {
            $context->buildViolation($message)
                ->atPath($field)
                ->addViolation();
        }
    }
}
