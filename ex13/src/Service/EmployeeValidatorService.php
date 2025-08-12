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

// 3. Un seul CEO dans l’entreprise (seulement si on essaie d'ajouter un nouveau CEO)
if ($employee->getPosition() === PositionEnum::CEO) {
    $existingCEO = $this->employeeRepository->findOneBy(['position' => PositionEnum::CEO]);
    if ($existingCEO && $existingCEO->getId() !== $employee->getId()) {
        $errors[] = 'There can only be one CEO in the company.';
        $this->addViolation($context, 'position', end($errors));
    }
}

    return $errors;
}



    /**
     * Vérifie si le CEO peut être supprimé.
     */
    public function canDeleteCEO(Employee $employee): bool
    {
        if ($employee->getPosition()?->value === 'CEO') {
            $totalEmployees = $this->employeeRepository->count([]);
            return $totalEmployees <= 1; // Suppression possible seulement si c'est le dernier employé
        }
        return true;
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
