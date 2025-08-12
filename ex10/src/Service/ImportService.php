<?php

namespace App\Service;

use Throwable;
use Doctrine\DBAL\Connection;
use App\Entity\Ex10OrmRecords;

class ImportService
{
    public function importFromFile(
        string $filename,
        Connection $connection,
        DataInsertServiceSQL $sqlInsertService,
        DataInsertServiceORM $ormInsertService
    ): array
    {
        $summary = [
            'success_sql' => 0,
            'success_orm' => 0,
            'errors' => [],
        ];

        try {
            if (!file_exists($filename) || !is_readable($filename)) {
                throw new \RuntimeException("Error, cannot read file $filename.");
            }

            $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line)
			{
                // Insert SQL
                try {
                    $msg = $sqlInsertService->insertRecord($connection, 'ex10_sql_records', $line, new \DateTime());
                    if (str_starts_with($msg, 'success:')) {
                        $summary['success_sql']++;
                    } else {
                        $summary['errors'][] = "SQL insert error for: '$line' ($msg)";
                    }
                } catch (Throwable $e) {
                    $summary['errors'][] = "SQL insert exception for: '$line' - " . $e->getMessage();
                }

                // Insert ORM
                try {
                    $record = new Ex10OrmRecords();
                    $record->setData($line);
                    $record->setDate(new \DateTime());
                    $ormInsertService->insertRecord($record);
                    $summary['success_orm']++;
                } catch (Throwable $e) {
                    $summary['errors'][] = "ORM insert exception for: '$line' - " . $e->getMessage();
                }
            }
        } catch (Throwable $e) {
            $summary['errors'][] = $e->getMessage();
        }

        return $summary;
    }
}
?>