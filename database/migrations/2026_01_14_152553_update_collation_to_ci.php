<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $databaseName};

            // Skip migrations table
            if ($tableName === 'migrations') {
                continue;
            }
            // Get all text-based columns with their collation
            $columns = DB::select("
            SELECT
                COLUMN_NAME as Field,
                COLUMN_TYPE as Type,
                COLLATION_NAME as Collation
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND DATA_TYPE IN ('varchar', 'char', 'text', 'tinytext', 'mediumtext', 'longtext')
        ", [$databaseName, $tableName]);
            foreach ($columns as $column) {
                $columnName = $column->Field;
                $columnType = $column->Type;
                // Skip if already case-insensitive or no collation
                if (empty($column->Collation) || str_contains(strtolower($column->Collation), '_ci')) {
                    continue;
                }
                // Determine the correct collation based on current collation
                $collation = str_contains(strtolower($column->Collation), 'utf8mb4')
                    ? 'utf8mb4_unicode_ci'
                    : 'utf8_unicode_ci';
                try {
                    DB::statement("ALTER TABLE `$tableName` MODIFY `$columnName` $columnType COLLATE $collation");
                } catch (\Exception $e) {
                    \Log::warning("Failed to update collation for $tableName.$columnName: " . $e->getMessage());
                    continue;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ci', function (Blueprint $table) {
            //
        });
    }
};
