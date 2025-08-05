<?php

namespace App\Service;

use Throwable;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TablesDeleteService
{
	private EntityManagerInterface $em;
    private SchemaTool $schemaTool;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->schemaTool = new SchemaTool($em);
    }

	public function dropTables(array $entityClasses): string
	{
		try
		{
			$metadata = [];
			foreach ($entityClasses as $class) {
				// Récupérer le metadata de la classe
				$meta = $this->em->getClassMetadata($class);
				// Vérifier si la table existe dans la base via la connexion
				$tableName = $meta->getTableName();
				$schemaManager = $this->em->getConnection()->createSchemaManager();

				if (!$schemaManager->tablesExist([$tableName])) {
					// Si une table n’existe pas, retourner un message info et stopper
					return "info:Table '$tableName' does not exist, no need to drop.";
				}

				$metadata[] = $meta;
			}

			// Si toutes les tables existent, on les supprime
			$this->schemaTool->dropSchema($metadata);

			return 'success:Success! Tables dropped successfully.';
		}
		catch (Throwable $e)
		{
			return 'danger:Error, we could not drop the tables : ' . $e->getMessage();
		}
	}


public function removeMaritalStatusProperly(string $entityFilePath, string $snippetFilePath, string $projectDir): string
{
    try {
        // 1. Générer le SQL de mise à jour (pour suppression colonne)
        $dumpProcess = new Process(['php', 'bin/console', 'doctrine:schema:update', '--dump-sql']);
        $dumpProcess->setWorkingDirectory($projectDir);
        $dumpProcess->run();
        if (!$dumpProcess->isSuccessful()) {
            throw new ProcessFailedException($dumpProcess);
        }
        $sqlDump = $dumpProcess->getOutput();

        // Optionnel : vérifier que la suppression de colonne est bien dans le dump
        if (strpos($sqlDump, 'DROP COLUMN marital_status') === false) {
            // Aucun changement nécessaire côté base
            // Tu peux décider de continuer ou non ici
        }

        // 2. Générer la migration (diff)
        $diffProcess = new Process(['php', 'bin/console', 'doctrine:migrations:diff']);
        $diffProcess->setWorkingDirectory($projectDir);
        $diffProcess->run();
        if (!$diffProcess->isSuccessful()) {
            throw new ProcessFailedException($diffProcess);
        }

        // 3. Exécuter la migration
        $migrateProcess = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
        $migrateProcess->setWorkingDirectory($projectDir);
        $migrateProcess->run();
        if (!$migrateProcess->isSuccessful()) {
            throw new ProcessFailedException($migrateProcess);
        }

        // 4. Supprimer la propriété dans le fichier PHP
        $personContent = file_get_contents($entityFilePath);
        $snippet = file_get_contents($snippetFilePath);

        if (strpos($personContent, $snippet) === false) {
            return 'info:Nothing to remove, MaritalStatus property not found in entity.';
        }

        $newContent = str_replace($snippet, '', $personContent);
        file_put_contents($entityFilePath, $newContent);

        return 'success:MaritalStatus removed cleanly, migration applied and entity updated.';
    } catch (Throwable $e) {
        return 'danger:Failed to remove MaritalStatus properly: ' . $e->getMessage();
    }
}


}

?>