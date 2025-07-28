<?php
namespace App\Enum;

enum EmployeePosition: string
{
    case Manager = 'manager';
    case AccountManager = 'account_manager';
    case QaManager = 'qa_manager';
    case DevManager = 'dev_manager';
    case Ceo = 'ceo';
    case Coo = 'coo';
    case BackendDev = 'backend_dev';
    case FrontendDev = 'frontend_dev';
    case QaTester = 'qa_tester';
}
