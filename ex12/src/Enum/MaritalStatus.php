<?php

namespace App\Enum;

enum MaritalStatus: string
{
    case SINGLE = 'single';
    case MARRIED = 'married';
    case WIDOWER = 'widower';
}