<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait DatabaseTrait
{
    public function getAllTableNames() : array
    {
        try {

            // Get the database connection name from the .env file
            $connection = config('database.default');
            $columnName = $connection === 'pgsql' 
                          ? 'table_name' 
                          : 'Tables_in_'.config('database.master_db');

            // Retrieve table names based on the database connection
            switch ($connection) {
                case 'mysql':
                    $tableNames = DB::select('SHOW TABLES');
                    break;

                case 'pgsql':
                    $tableNames = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ?', ['public']);
                    break;

                default:
                    // Handle unsupported database types or throw an exception
                    throw new \Exception("Unsupported database connection: $connection");
            }

           return $this->transformTableNames($tableNames,$columnName);
        } catch (\Exception $e) {
            Log::error("getAllTableNames {$e->getMessage()}");
            return [];
        }
    }

    public function transformTableNames(array $data, string $propertyName = 'table_name', string $excludeTableName = 'migrations'): array
    {
        // Use pluck to extract the specified property values
        $tableNames = collect($data)->pluck($propertyName)->toArray();
    
        // Remove the specified table name if provided
        if ($excludeTableName !== null) {
            $tableNames = array_values(array_diff($tableNames, [$excludeTableName]));
        }
    
        return $tableNames ? $tableNames : [];
    }

    public function getCreateSqlStatement(string | array $tableName) : string  {
        try {

            $connection = config('database.default');

            switch ($connection) {
                case 'mysql':
                    $createStatementQuery = DB::select("SHOW CREATE TABLE {$tableName}");
                    $createStatementResult = $createStatementQuery[0]->{'Create Table'};
                    break;
    
                case 'pgsql':
                    $createStatementQuery = DB::select("SELECT generate_create_table_statement(:table_name) as create_statement", ['table_name' => $tableName]);
                    dd($createStatementQuery);
                    break;
    
                default:
                   $createStatementQuery = '';
                   $createStatementResult = '';
                   break;
            }
    
            return $createStatementResult ? $createStatementResult : '';
        } catch (\Exception $e) {
            Log::error("getAllTableNames {$e->getMessage()}");
            return '';
        }

    }
}
