<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ValidationService
{
    public function validateQueryParams(Request $request): array
    {
        $Messages = [];

        $filterName = trim($request->query->get('filter_name', ''));
        if (mb_strlen($filterName) > 80)
        {
            $filterName = mb_substr($filterName, 0, 80);
            $Messages[] = ['warning', 'Filter truncated to 80 characters.'];
        }
        if ($filterName && !preg_match('/^[\p{L}\p{N} _\'\-]*$/u', $filterName))
        {
            $Messages[] = ['danger', 'Filter contains invalid characters. Only letters, digits, spaces, dash, underscore, apostrophe allowed.'];
            $filterName = '';
        }

        $allowedSorts = ['name', 'email', 'birthdate'];
        $sortBy = $request->query->get('sort_by', 'name');
        if (!in_array($sortBy, $allowedSorts, true))
        {
            $Messages[] = ['danger', 'Invalid sorting field. Using default.'];
            $sortBy = 'name';
        }

        $allowedDir = ['asc', 'desc'];
        $sortDir = $request->query->get('sort_dir', 'asc');
        if (!in_array($sortDir, $allowedDir, true))
        {
            $Messages[] = ['danger', 'Invalid sorting direction. Using ascending.'];
            $sortDir = 'asc';
        }

        $limit = 100;

        return [
            'filter_name' => $filterName,
            'sort_by'     => $sortBy,
            'sort_dir'    => $sortDir,
            'limit'       => $limit,
            'flash_messages' => $Messages,
        ];
    }
}

