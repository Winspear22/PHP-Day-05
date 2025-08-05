<?php

namespace App\Service;

use Throwable;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TablesMigrationService
{
    private string $entityFilePath;
    private string $snippetFilePath;
	
	private string $projectDir;


    public function __construct(string $entityFilePath, string $snippetFilePath, string $projectDir)
    {
        $this->entityFilePath = $entityFilePath;
        $this->snippetFilePath = $snippetFilePath;
		$this->projectDir = $projectDir;
    }

public function migrate(): array
{
    try {
        // Run the diff command with option to allow empty diff
        $diffProcess = new Process(['php', 'bin/console', 'doctrine:migrations:diff', '--allow-empty-diff']);
        $diffProcess->setWorkingDirectory($this->projectDir);
        $diffProcess->run();

        $diffOutput = $diffProcess->getOutput();
        $diffError = $diffProcess->getErrorOutput();

        if (!$diffProcess->isSuccessful()) {
            throw new ProcessFailedException($diffProcess);
        }

        // Check if diffOutput contains 'No changes detected' or similar message
        if (strpos($diffOutput, 'No changes detected') !== false) {
            return ['type' => 'info', 'message' => 'No changes detected: tables are already up to date.'];
        }

        // Run the migration
        $migrateProcess = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
        $migrateProcess->setWorkingDirectory($this->projectDir);
        $migrateProcess->run();

        if (!$migrateProcess->isSuccessful()) {
            throw new ProcessFailedException($migrateProcess);
        }

        return ['type' => 'success', 'message' => "Migration successfully executed.\n" . $migrateProcess->getOutput()];
    } catch (Throwable $e) {
        return ['type' => 'danger', 'message' => 'Error during migration: ' . $e->getMessage()];
    }
}



	public function updateSchema(): array
	{
		try
		{
			$personContent = file_get_contents($this->entityFilePath);
			$snippet = file_get_contents($this->snippetFilePath);

			if (strpos($personContent, $snippet) !== false) {
				// Le snippet est déjà présent, on informe l'utilisateur
				return ['type' => 'info', 'message' => 'MaritalStatus property already present in entity, skipping injection.'];
			}

			// Sinon on injecte le snippet et on update le schema
			$this->injectMaritalStatusProperty();

			$process = new Process(['php', 'bin/console', 'doctrine:schema:update', '--force']);
			$process->setWorkingDirectory($this->projectDir);
			$process->run();

			if (!$process->isSuccessful()) {
				throw new ProcessFailedException($process);
			}

			return ['type' => 'success', 'message' => 'Database schema updated successfully.'];
		}
		catch (Throwable $e)
		{
			return ['type' => 'danger', 'message' => 'Error, we could not update the schema: ' . $e->getMessage()];
		}
	}

	public function injectMaritalStatusProperty(): void
	{
		$personContent = file_get_contents($this->entityFilePath);
		$snippet = file_get_contents($this->snippetFilePath);

		// Insertion juste avant le dernier '}'
		$position = strrpos($personContent, '}');
		if ($position === false)
			throw new RuntimeException("Error, malformed entity file: cannot find closing brace.");

		$newContent = substr_replace($personContent, "\n\n    " . $snippet . "\n\n}", $position, 1);
		file_put_contents($this->entityFilePath, $newContent);
	}

// src/Service/TablesMigrationService.php

// src/Service/TablesMigrationService.php

public function removeMaritalStatusCleanly(): array
{
    try {
        $personContent = file_get_contents($this->entityFilePath);
        $snippet = file_get_contents($this->snippetFilePath);

        // 1. Vérifier d’abord que la propriété existe
        if (strpos($personContent, $snippet) === false) {
            return ['type' => 'info', 'message' => 'MaritalStatus property not found in entity, nothing to remove.'];
        }

        // 2. Générer migration de suppression propre (avant de modifier le fichier !)
        $process = new Process(['php', 'bin/console', 'doctrine:migrations:diff']);
        $process->setWorkingDirectory($this->projectDir);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // 3. Exécuter immédiatement la migration générée
        $migrateProcess = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
        $migrateProcess->setWorkingDirectory($this->projectDir);
        $migrateProcess->run();

        if (!$migrateProcess->isSuccessful()) {
            throw new ProcessFailedException($migrateProcess);
        }

        // 4. Seulement maintenant, retirer le snippet du fichier PHP
        $newContent = str_replace($snippet, '', $personContent);
        file_put_contents($this->entityFilePath, $newContent);

        // 5. (Important !) Vider les caches Doctrine pour synchroniser les métadonnées
        $cacheClearProcess = new Process(['php', 'bin/console', 'cache:clear']);
        $cacheClearProcess->setWorkingDirectory($this->projectDir);
        $cacheClearProcess->run();

        if (!$cacheClearProcess->isSuccessful()) {
            throw new ProcessFailedException($cacheClearProcess);
        }

        return ['type' => 'success', 'message' => 'MaritalStatus removed successfully from entity and schema updated.'];
    } catch (Throwable $e) {
        return ['type' => 'danger', 'message' => 'Failed to remove MaritalStatus cleanly: ' . $e->getMessage()];
    }
}


}
