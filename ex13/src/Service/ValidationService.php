<?php

namespace App\Service;

use App\Entity\Employee;
use DateTime;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ValidationService
{
    public static function validateDates(Employee $employee, ExecutionContextInterface $context): void
    {
        $birthdate = $employee->getBirthdate();
        $employedSince = $employee->getEmployedSince();
        $employedUntil = $employee->getEmployedUntil();
        $todayDate = new DateTime();

        if (!$birthdate || !$employedSince)
		{
            return;
        }

        // Birthdate dans le futur
        if ($birthdate > $todayDate) {
            $context->buildViolation('Error. Birthdate cannot be in the future!')
                ->atPath('birthdate')
                ->addViolation();
        }

        // Embauche avant la naissance
        if ($employedSince < $birthdate) {
            $context->buildViolation('Error. Hire date cannot be before birth date.')
                ->atPath('employed_since')
                ->addViolation();
        }

        // Moins de 18 ans à l'embauche
        $minHireDate = (clone $birthdate)->modify('+18 years');
        if ($employedSince < $minHireDate) {
            $context->buildViolation('Error. Employee must be at least 18 years old to be hired.')
                ->atPath('employed_since')
                ->addViolation();
        }

        // Contrat d'au moins 24h
        if ($employedUntil && $employedUntil < (clone $employedSince)->modify('+1 day')) {
            $context->buildViolation('Error. Contract end date must be at least 24 hours after hire date.')
                ->atPath('employed_until')
                ->addViolation();
        }
    }
}
