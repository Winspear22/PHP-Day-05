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

private const EXPECTED_SNIPPET_CONTENT = <<<CODE
    #[ORM\Column(enumType: MaritalStatus::class, options: ["default" => "single"])]
    private ?MaritalStatus \$marital_status = MaritalStatus::SINGLE;


    public function getMaritalStatus(): ?MaritalStatus
    {
        return \$this->marital_status;
    }

    public function setMaritalStatus(MaritalStatus \$marital_status): static
    {
        \$this->marital_status = \$marital_status;

        return \$this;
    }
CODE;


    public function __construct(string $entityFilePath, string $snippetFilePath, string $projectDir)
    {
        $this->entityFilePath = $entityFilePath;
        $this->snippetFilePath = $snippetFilePath;
		$this->projectDir = $projectDir;
    }
	public function getSnippetFilePath(): string
	{
		return $this->snippetFilePath;
	}
	public function updateSchema(): array
	{
		try
		{
			$personContent = file_get_contents($this->entityFilePath);
			if ($personContent === false)
				throw new RuntimeException("Error: Unable to read entity file at '{$this->entityFilePath}'.");

			$snippet = file_get_contents($this->snippetFilePath);
			if ($snippet === false)
				throw new RuntimeException("Error: Unable to read snippet file at '{$this->snippetFilePath}'.");
			if (trim($snippet) === '')
				throw new RuntimeException("Error: The snippet file '{$this->snippetFilePath}' is empty.");
			if (trim($snippet) !== trim(self::EXPECTED_SNIPPET_CONTENT))
				throw new RuntimeException("Error: The snippet file '{$this->snippetFilePath}' does not match the expected content.");
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
		if ($personContent === false)
			throw new RuntimeException("Error: Unable to read entity file at '{$this->entityFilePath}'.");

		$snippet = file_get_contents($this->snippetFilePath);
		if ($snippet === false)
			throw new RuntimeException("Error: Unable to read snippet file at '{$this->snippetFilePath}'.");

		if (trim($snippet) === '')
			throw new RuntimeException("Error: The snippet file '{$this->snippetFilePath}' is empty.");
		if (trim($snippet) !== trim(self::EXPECTED_SNIPPET_CONTENT))
			throw new RuntimeException("Error: The snippet file '{$this->snippetFilePath}' does not match the expected content.");

		// Insertion juste avant le dernier '}'
		$position = strrpos($personContent, '}');
		if ($position === false)
			throw new RuntimeException("Error, malformed entity file: cannot find closing brace.");

		$newContent = substr_replace($personContent, "\n\n    " . $snippet . "\n\n}", $position, 1);

		if (file_put_contents($this->entityFilePath, $newContent) === false)
			throw new RuntimeException("Error: Unable to write to entity file at '{$this->entityFilePath}'. Check file permissions.");
	}

}
