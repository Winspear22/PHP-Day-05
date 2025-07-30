<?php

namespace App\Service;

use Throwable;
use Doctrine\DBAL\Connection;

class TablesFillService
{
    public function fillAll(Connection $connection, int $nbPersons = 10): array
    {
        $personsTable      = 'ex11_persons';
        $addressesTable    = 'ex11_addresses';
        $bankAccountsTable = 'ex11_bank_accounts';

        $messages = [];
        try
        {
            $connection->executeStatement("DELETE FROM $addressesTable");
            $connection->executeStatement("DELETE FROM $bankAccountsTable");
            $connection->executeStatement("DELETE FROM $personsTable");
            $messages[] = "success:All ex11 tables have been emptied.";

            $messages[] = $this->fillPersons($connection, $nbPersons, $personsTable);
            $messages[] = $this->fillAddresses($connection, $addressesTable, $personsTable);
            $messages[] = $this->fillBankAccounts($connection, $bankAccountsTable, $personsTable);

            return $messages;
        }
        catch (Throwable $e)
        {
            $messages[] = "danger:Error during table filling: " . $e->getMessage();
            return $messages;
        }
    }

    public function fillPersons(Connection $connection, int $nb = 10, string $tableName): string
    {
        try
        {
            $maritalStatuses = ['single', 'married', 'widower'];
            for ($i = 0; $i < $nb; $i++)
            {
                $username = 'user' . uniqid();
                $name = 'Name' . $i;
                $email = 'user' . $i . '@test.com';
                $enable = rand(0, 1);
                $birthdate = date('Y-m-d H:i:s', strtotime('-' . rand(20, 40) . ' years'));
                $marital_status = $maritalStatuses[array_rand($maritalStatuses)];
                $connection->executeStatement(
                    "INSERT INTO $tableName (username, name, email, enable, birthdate, marital_status)
                    VALUES (?, ?, ?, ?, ?, ?)",
                    [$username, $name, $email, $enable, $birthdate, $marital_status]
                );
            }
            return "success:Success! Table $tableName was filled with $nb entries.";
        }
        catch (Throwable $e)
        {
            return "danger:Error while filling $tableName: " . $e->getMessage();
        }
    }

    public function fillAddresses(Connection $connection, string $addressTable, string $personsTable): string
    {
        try
        {
            // Get all existing person IDs
            $personIds = $connection->fetchFirstColumn("SELECT id FROM $personsTable");
            if (!$personIds)
                return "danger:Error, no person was found.";

            foreach ($personIds as $personId)
            {
                $nbAddresses = rand(1, 2);
                for ($j = 0; $j < $nbAddresses; $j++)
                {
                    $address = "Fictive address #" . rand(1000, 9999);
                    $connection->executeStatement(
                        "INSERT INTO $addressTable (address, person_id) VALUES (?, ?)",
                        [$address, $personId]
                    );
                }
            }
            return "success:Success! $addressTable filled (1 or 2 per person).";
        }
        catch (Throwable $e)
        {
            return "danger:Error while filling $addressTable: " . $e->getMessage();
        }
    }

    public function fillBankAccounts(Connection $connection, string $bankAccountsTable, string $personsTable): string
    {
        try
        {
            // Get all existing person IDs
            $personIds = $connection->fetchFirstColumn("SELECT id FROM $personsTable");
            if (!$personIds)
                return "danger:Error, no person found.";

            foreach ($personIds as $personId)
            {
                $iban = 'FR' . rand(1000000000, 9999999999) . rand(1000, 9999);
                $bankName = "Bank #" . rand(1, 5);
                $connection->executeStatement(
                    "INSERT INTO $bankAccountsTable (iban, bank_name, person_id) VALUES (?, ?, ?)",
                    [$iban, $bankName, $personId]
                );
            }
            return "success:Success! $bankAccountsTable filled (1 account per person).";
        }
        catch (Throwable $e)
        {
            return "danger:Error while filling $bankAccountsTable: " . $e->getMessage();
        }
    }
}
