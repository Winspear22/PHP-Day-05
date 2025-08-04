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

    /*public function migrate(): string
    {
		try
		{
			$process = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
			$process->setWorkingDirectory($this->projectDir);  // <-- IMPORTANT
			$process->run();


			if (!$process->isSuccessful())
				throw new ProcessFailedException($process);
			return $process->getOutput();
		}
		catch (Throwable $e)
		{
			return 'danger:Error, migration failed: ' . $e->getMessage();
		}
    }*/

	public function migrate(): string
	{
		try {
			// Génère une nouvelle migration en comparant les entités à la base
			$diffProcess = new Process(['php', 'bin/console', 'doctrine:migrations:diff']);
			$diffProcess->setWorkingDirectory($this->projectDir);
			$diffProcess->run();

			if (!$diffProcess->isSuccessful()) {
				throw new ProcessFailedException($diffProcess);
			}
			
			// Exécute la migration générée
			$migrateProcess = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
			$migrateProcess->setWorkingDirectory($this->projectDir);
			$migrateProcess->run();

			if (!$migrateProcess->isSuccessful()) {
				throw new ProcessFailedException($migrateProcess);
			}

			return $diffProcess->getOutput() . "\n" . $migrateProcess->getOutput();
		} catch (Throwable $e) {
			return 'danger:Error during migration: ' . $e->getMessage();
		}
	}


	public function updateSchema(): array
	{
		try
		{
			$personContent = file_get_contents($this->entityFilePath);
			$snippet = file_get_contents($this->snippetFilePath);

			if (strpos($personContent, $snippet) !== false)
				return ['type' => 'info', 'message' => 'MaritalStatus property already present in entity, skipping injection.'];
			$this->injectMaritalStatusProperty();

			$process = new Process(['php', 'bin/console', 'doctrine:schema:update', '--force']);
			$process->setWorkingDirectory($this->projectDir);
			$process->run();

			if (!$process->isSuccessful())
				throw new ProcessFailedException($process);

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

}
