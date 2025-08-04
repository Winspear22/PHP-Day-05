<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Throwable;

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
            foreach ($entityClasses as $class)
                $metadata[] = $this->em->getClassMetadata($class);
            $this->schemaTool->dropSchema($metadata);

            return 'success:Success! Tables dropped successfully.';
        }
		catch (Throwable $e)
		{
            return 'danger:Error, we could not drop the tables : ' . $e->getMessage();
        }
    }

	/*public function removeMaritalStatusProperty(string $entityFilePath, string $snippetFilePath): string
	{
		try
		{
			$personContent = file_get_contents($entityFilePath);
			$snippet = file_get_contents($snippetFilePath);

			// On enlève la portion du snippet (propriété marital_status) du contenu de Person.php
			// Le snippet est supposé être exactement ce que tu as injecté
			$newContent = str_replace($snippet, '', $personContent);

			file_put_contents($entityFilePath, $newContent);

			return 'success:Success! MaritalStatus property removed from entity.';
		}
		catch (Throwable $e)
		{
			return 'danger:Error removing MaritalStatus property: ' . $e->getMessage();
		}
	}*/

	public function removeMaritalStatusProperty(string $entityFilePath, string $snippetFilePath): string
	{
		try
		{
			$personContent = file_get_contents($entityFilePath);
			$snippet = file_get_contents($snippetFilePath);

			// Vérifie si le snippet est présent dans le fichier avant suppression
			if (strpos($personContent, $snippet) === false) {
				return 'info:Nothing to remove, MaritalStatus property not found in entity.';
			}

			// Supprime la portion du snippet (propriété marital_status) du contenu
			$newContent = str_replace($snippet, '', $personContent);

			file_put_contents($entityFilePath, $newContent);

			return 'success:Success! MaritalStatus property removed from entity.';
		}
		catch (Throwable $e)
		{
			return 'danger:Error removing MaritalStatus property: ' . $e->getMessage();
		}
	}

}

?>