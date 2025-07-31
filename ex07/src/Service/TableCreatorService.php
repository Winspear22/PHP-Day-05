<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Throwable;

class TableCreatorService
{
    public function createTable(EntityManagerInterface $em, string $entityClass, string $tableName): string
    {
        try
        {
            $schemaManager = $em->getConnection()->createSchemaManager();
            $schemaTool = new SchemaTool($em);
            $metadata = [$em->getClassMetadata($entityClass)];

            if ($schemaManager->tablesExist([$tableName]))
                return "info:The table $tableName already exists.";
            $schemaTool->createSchema($metadata);
            return "success:Success! The table $tableName was created.";
        }
        catch (Throwable $e)
        {
            return "danger:Error, we could not create the table $tableName : " . $e->getMessage();
        }
    }
    public function checkTableExistence(EntityManagerInterface $em, string $tableName): bool
    {
        try
        {
            $schemaManager = $em->getConnection()->createSchemaManager();
            return $schemaManager->tablesExist([$tableName]);
        }
        catch (Throwable $e)
        {
            return false;
        }
    }
}
