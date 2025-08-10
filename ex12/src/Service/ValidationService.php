<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class ValidationService
{
    public function validateStringLength(string $value, int $maxLength): bool
    {
        return mb_strlen($value) <= $maxLength;
    }

    public function validateQueryParams(Request $request): array
    {
        $flashMessages = [];

        // Valeurs par défaut
        $filterName = trim($request->query->get('filter_name', ''));
        $sortBy     = $request->query->get('sort_by', 'id');
        $sortDir    = strtolower($request->query->get('sort_dir', 'asc'));
        $limit      = (int) $request->query->get('limit', 20);

        // Validation du nom
        if ($filterName && !$this->validateStringLength($filterName, 50)) {
            $flashMessages[] = ['danger', 'Filter name is too long (max 50 chars).'];
            $filterName = '';
        }

        // Validation du tri
        $allowedSortBy = ['id', 'name', 'email', 'birthdate'];
        if (!in_array($sortBy, $allowedSortBy, true)) {
            $flashMessages[] = ['danger', 'Invalid sort field.'];
            $sortBy = 'id';
        }

        // Validation de la direction
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $flashMessages[] = ['danger', 'Invalid sort direction.'];
            $sortDir = 'asc';
        }

        // Validation du limit
        if ($limit <= 0 || $limit > 100) {
            $flashMessages[] = ['danger', 'Invalid limit.'];
            $limit = 20;
        }

        return [
            'filter_name'    => $filterName,
            'sort_by'        => $sortBy,
            'sort_dir'       => $sortDir,
            'limit'          => $limit,
            'flash_messages' => $flashMessages,
        ];
    }
}
