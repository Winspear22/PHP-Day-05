<?php

namespace App\Controller;

use Throwable;
use RuntimeException;
use Doctrine\DBAL\Connection;
use App\Service\TablesAlterService;
use App\Service\TablesCreatorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class Ex08Controller extends AbstractController
{
    /**
     * @Route("/ex06", name="ex06_index")
     */
    public function index(): Response
    {  
        return $this->render('ex08/index.html.twig');
    }

    /**
    * @Route("/ex08/create_persons", name="ex08_create_persons", methods={"POST"})
    */
    public function createPersonsTable(TablesCreatorService $tablesCreator, Connection $connection): Response
    {
        try
        {
            $result = $tablesCreator->createPersonsTable($connection, 'persons');
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex08_index');
    }

    /**
     * @Route("/ex08/create_bank_accounts", name="ex08_create_bank_accounts", methods={"POST"})
     */
    public function createBankAccountsTable(TablesCreatorService $tablesCreator, Connection $connection): Response
    {
        try
        {
            $result = $tablesCreator->createBankAccountsTable($connection, 'bank_accounts');
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex08_index');
    }

    /**
     * @Route("/ex08/create_addresses", name="ex08_create_addresses", methods={"POST"})
     */
    public function createAddressesTable(TablesCreatorService $tablesCreator, Connection $connection): Response
    {
        try
        {
            $result = $tablesCreator->createAddressesTable($connection, 'addresses');
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex08_index');
    }

    /**
     * @Route("/ex08/add_marital_status", name="ex08_add_marital_status", methods={"POST"})
     */
    public function addMaritalStatusToPersons(TablesAlterService $tablesAlter, Connection $connection): Response
    {
        try
        {
            $result = $tablesAlter->addMaritalStatusToPersons($connection, 'persons');
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex08_index');
    }

    /**
     * @Route("/ex08/relation_bank_account", name="ex08_relation_bank_account", methods={"POST"})
     */
    public function createBankAccountRelation(TablesAlterService $tablesAlter, Connection $connection): Response
    {
        try
        {
            $result = $tablesAlter->createBankAccountRelation($connection);
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex08_index');
    }

    /**
     * @Route("/ex08/relation_address", name="ex08_relation_address", methods={"POST"})
     */
    public function createAddressRelation(TablesAlterService $tablesAlter, Connection $connection): Response
    {
        try
        {
            $result = $tablesAlter->createAddressRelation($connection);
            [$type, $msg] = explode(':', $result, 2);
            $this->addFlash($type, $msg);
        }
        catch (Throwable $e)
        {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
        }
        return $this->redirectToRoute('ex08_index');
    }



}
