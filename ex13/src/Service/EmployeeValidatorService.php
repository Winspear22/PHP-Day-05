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

    /**
     * Valide toutes les règles métier pour un employé donné
     */
    public function validateEmployee(Employee $employee, ?ExecutionContextInterface $context = null): array
    {
        $errors = [];

        // CEO
        $errors = array_merge($errors, $this->validateCEO($employee, $context));

        // COO
        $errors = array_merge($errors, $this->validateCOO($employee, $context));

        // Managers
        $errors = array_merge($errors, $this->validateManagers($employee, $context));

        return $errors;
    }

    /**
     * Règles pour le CEO
     */
    public function validateCEO(Employee $employee, ?ExecutionContextInterface $context = null): array
    {
        $errors = [];

        if ($employee->getId() === null && $this->employeeRepository->count([]) === 0) {
            if ($employee->getPosition() !== PositionEnum::CEO) {
                $errors[] = 'The first employee must be the CEO.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        if ($employee->getPosition() === PositionEnum::CEO && $employee->getManager() !== null) {
            $errors[] = 'The CEO cannot have a manager.';
            $this->addViolation($context, 'manager', end($errors));
        }

        if ($employee->getPosition() === PositionEnum::CEO) {
            $existingCEO = $this->employeeRepository->findOneBy(['position' => PositionEnum::CEO]);
            if ($existingCEO && $existingCEO->getId() !== $employee->getId()) {
                $errors[] = 'There can only be one CEO in the company.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        $currentCEO = $this->employeeRepository->findOneBy(['position' => PositionEnum::CEO]);
        if ($currentCEO && $currentCEO->getId() === $employee->getId()) {
            if ($employee->getPosition() !== PositionEnum::CEO) {
                $errors[] = 'The CEO position cannot be changed.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        return $errors;
    }

    /**
     * Règles pour le COO
     */
    public function validateCOO(Employee $employee, ?ExecutionContextInterface $context = null): array
    {
        $errors = [];

        if ($this->employeeRepository->count([]) === 1 && $employee->getId() === null) {
            if ($employee->getPosition() !== PositionEnum::COO) {
                $errors[] = 'The second employee must be the COO.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        if ($employee->getPosition() === PositionEnum::COO) {
            $existingCOO = $this->employeeRepository->findOneBy(['position' => PositionEnum::COO]);
            if ($existingCOO && $existingCOO->getId() !== $employee->getId()) {
                $errors[] = 'There can only be one COO in the company.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        if ($employee->getPosition() === PositionEnum::COO && $employee->getManager()?->getPosition() !== PositionEnum::CEO) {
            $errors[] = 'The COO must have the CEO as manager.';
            $this->addViolation($context, 'manager', end($errors));
        }

        $currentCOO = $this->employeeRepository->findOneBy(['position' => PositionEnum::COO]);
        if ($currentCOO && $currentCOO->getId() === $employee->getId()) {
            if ($employee->getPosition() !== PositionEnum::COO) {
                $errors[] = 'The COO position cannot be changed.';
                $this->addViolation($context, 'position', end($errors));
            }
        }

        return $errors;
    }

    /**
     * Règles pour les Managers
     */
    public function validateManagers(Employee $employee, ?ExecutionContextInterface $context = null): array
    {
        $errors = [];

        $managerTypes = [
            PositionEnum::MANAGER,
            PositionEnum::ACCOUNT_MANAGER,
            PositionEnum::QA_MANAGER,
            PositionEnum::DEV_MANAGER
        ];

        if (!in_array($employee->getPosition(), $managerTypes, true)) {
            return $errors; // pas un manager, pas de règles
        }

        // Règle 5 : doit avoir COO comme manager
        if ($employee->getManager()?->getPosition() !== PositionEnum::COO) {
            $errors[] = 'Managers can only report to the COO.';
            $this->addViolation($context, 'manager', end($errors));
        }

        // Règles 1, 2, 3 : limitations sur qui ils peuvent manager
        foreach ($employee->getEmployees() as $managed) {
            if (in_array($managed->getPosition(), $managerTypes, true)) {
                $errors[] = 'A manager cannot manage another manager.';
                $this->addViolation($context, 'manager', end($errors));
            }

            if (!$this->canManagePosition($employee->getPosition(), $managed->getPosition())) {
                $errors[] = sprintf(
                    'A %s cannot manage a %s.',
                    $employee->getPosition()->value,
                    $managed->getPosition()->value
                );
                $this->addViolation($context, 'manager', end($errors));
            }
        }

        return $errors;
    }

    /**
     * Qui peut manager qui ?
     */
    private function canManagePosition(PositionEnum $managerPosition, PositionEnum $employeePosition): bool
    {
        return match ($managerPosition) {
            PositionEnum::QA_MANAGER => $employeePosition === PositionEnum::QA_TESTER,
            PositionEnum::DEV_MANAGER => in_array($employeePosition, [PositionEnum::FRONTEND_DEV, PositionEnum::BACKEND_DEV], true),
            PositionEnum::MANAGER => in_array($employeePosition, [PositionEnum::QA_TESTER, PositionEnum::FRONTEND_DEV, PositionEnum::BACKEND_DEV], true),
            PositionEnum::ACCOUNT_MANAGER => false,
            default => false
        };
    }

    /**
     * Suppression CEO
     */
    public function canDeleteCEO(Employee $employee): bool
    {
        $currentCEO = $this->employeeRepository->findOneBy(['position' => PositionEnum::CEO]);
        if ($currentCEO && $currentCEO->getId() === $employee->getId()) {
            $totalEmployees = $this->employeeRepository->count([]);
            return $totalEmployees <= 1;
        }
        return true;
    }

    /**
     * Suppression COO
     */
    public function canDeleteCOO(Employee $employee): bool
    {
        $currentCOO = $this->employeeRepository->findOneBy(['position' => PositionEnum::COO]);
        if ($currentCOO && $currentCOO->getId() === $employee->getId()) {
            return count($employee->getEmployees()) === 0;
        }
        return true;
    }

    /**
     * Suppression Manager
     */
    public function canDeleteManager(Employee $employee): bool
    {
        $managerTypes = [
            PositionEnum::MANAGER,
            PositionEnum::ACCOUNT_MANAGER,
            PositionEnum::QA_MANAGER,
            PositionEnum::DEV_MANAGER
        ];

        if (in_array($employee->getPosition(), $managerTypes, true)) {
            return count($employee->getEmployees()) === 0;
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
