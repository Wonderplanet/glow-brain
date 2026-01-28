<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class GenericModel extends BaseModel
{
    protected $table;
    public $timestamps = false; // タイムスタンプを使用しない
    protected $guarded = [];

    // 子クラスでオーバライドして、対象テーブルを絞るためのprefix
    protected string $tablePrefix = '';

    /**
     * @param string $tableName
     * @return $this
     */
    public function setTableName(string $tableName): self
    {
        $this->table = $tableName;
        return $this;
    }

    /**
     * @return Connection
     */
    public function getDBConnection(): Connection
    {
        return DB::connection($this->getConnectionName());
    }

    public function getDBBuilder(): Builder
    {
        return $this->getDBConnection()->table($this->table);
    }

    public function getDBName(): string
    {
        return config("database.connections.{$this->getConnectionName()}.database");;
    }

    /**
     * @return array
     */
    public function showTables(): array
    {
        $key = "Tables_in_{$this->getDBName()}";
        $tables = $this->getDBConnection()->select('SHOW TABLES');
        $tableNames = array_map(fn($row) => $row->$key, $tables);

        if ($this->tablePrefix) {
            $tableNames = array_filter($tableNames, fn($table) => str_starts_with($table, $this->tablePrefix));
        }

        return array_values($tableNames); // インデックス詰め直す
    }

    /**
     * テーブルのメタ情報を取得
     * @return array
     */
    public function getTableMeta(): array
    {
        $meta = $this->getDBConnection()
            ->table('information_schema.tables')
            ->select([
                // 大文字の場合と小文字の場合がある為、小文字で統一
                'table_comment as table_comment',
                'create_time as create_time',
                'update_time as update_time',
            ])
            ->where('table_schema', $this->getDBName())
            ->where('table_name', $this->table)
            ->first();

        return [
            'comment' => $meta?->table_comment ?? '',
            'created_at' => $meta?->create_time ?? null,
            'updated_at' => $meta?->update_time ?? null,
        ];
    }

    public function getColumns(): Collection
    {
        return $this->getDBConnection()
            ->table('information_schema.columns')
            ->select([
                // 大文字の場合と小文字の場合がある為、小文字で統一
                'ordinal_position as ordinal_position',
                'column_name as column_name',
                'is_nullable as is_nullable',
                'data_type as data_type',
                'character_maximum_length as character_maximum_length',
                'column_type as column_type',
                'column_comment as column_comment',
            ])
            ->where('table_schema', $this->getDBName())
            ->where('table_name', $this->table)
            ->orderBy('ordinal_position')
            ->get();
    }

    public function getUniqueColumns(): array
    {
        $result = $this->getDBConnection()
            ->table('information_schema.statistics')
            ->select('column_name')
            ->distinct()
            ->where('table_schema', $this->getDBName())
            ->where('table_name', $this->table)
            ->where('non_unique', 0)
            ->get();

        return $result->pluck('column_name')->toArray();
    }

    public function createBaseRecord(): static
    {
        $columns = $this->getColumns();
        $baseRecord = [];
        foreach ($columns as $column) {
            $baseRecord[$column->column_name] = match ($column->data_type) {
                'int', 'bigint', 'smallint', 'tinyint' => 0,
                'varchar', 'char', 'text', 'mediumtext', 'longtext' => '',
                'datetime', 'timestamp' => now()->toDateTimeString(),
                'date' => now()->toDateString(),
                'decimal', 'float', 'double' => 0.0,
                'boolean' => false,
                'json' => json_encode(['key' => '', 'value' => 0, 'flag' => false]),
                'enum' => preg_match("/^enum\((.*)\)$/", $column->column_type, $m) ? str_getcsv($m[1], ',', "'")[0] : '',
                default => null,
            };
        }
        return $this->newInstance($baseRecord);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return Collection
     */
    public function fetchAll(
        int $offset = 0,
        int $limit = 1000
    ): Collection {
        return $this->newQuery()
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * @param CarbonImmutable $startDate
     * @param CarbonImmutable $endDate
     * @param int $offset
     * @param int $limit
     * @return Collection
     */
    public function fetchByDateRange(
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        int $offset = 0,
        int $limit = 1000
    ): Collection {
        return $this->newQuery()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
}
