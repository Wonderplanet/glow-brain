<?php

namespace Database\Seeders\Dummies;

use Illuminate\Database\Seeder;

/**
 * テーブルのダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyGenericMstSeeder"
 */
class DummyGenericSeeder extends Seeder
{
    public int $numberOfRecords = 300;
    protected string $tableName = '';

    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        $genericModel = new $this->tableName();
        $mstTables = $genericModel->showTables();
        foreach ($mstTables as $tableName) {
            $records = collect();
            $model = (new $this->tableName())->setTableName($tableName);
            $columns = $model->getColumns();
            for ($i = 0; $i < $this->numberOfRecords; $i++) {
                $record = [];
                foreach ($columns as $column) {
                    $record[$column->column_name] = $this->generateRandomValue(
                        $column->data_type,
                        $column->column_type
                    );
                }
                $records->push($record);
            }
            $model->getDBBuilder()->upsert($records->toArray(), $model->getUniqueColumns());
        }
    }

    private function generateRandomValue(string $dataType, string $columnType): mixed
    {
        return match ($dataType) {
            'int', 'bigint', 'smallint' => rand(1, 999),
            'tinyint' => rand(0, 1),
            'varchar', 'char', 'text', 'mediumtext', 'longtext' => str()->random(10),
            'datetime', 'timestamp' => now()->subDay()->startOfDay()->addHours(mt_rand(0, 23))->toDateTimeString(),
            'date' => now()->subDay()->startOfDay()->addHours(mt_rand(0, 23))->toDateString(),
            'decimal', 'float', 'double' => round(mt_rand(100, 10000) / 100, 2),
            'boolean' => (bool)rand(0, 1),
            'json' => json_encode([
                'key' => str()->random(5),
                'value' => rand(0, 100),
                'flag' => (bool)rand(0, 1),
            ]),
            'enum' => $this->generateRandomEnumValue($columnType),
            default => null,
        };
    }

    private function generateRandomEnumValue(string $columnType): string
    {
        // 例: enum('small','medium','large')
        if (preg_match("/^enum\((.*)\)$/", $columnType, $matches)) {
            $values = str_getcsv($matches[1], ',', "'");
            return $values[array_rand($values)];
        }
        return '';
    }
}
