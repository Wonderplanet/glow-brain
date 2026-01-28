<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait MigrationAddColumnCommentsTrait
{
    /**
     * テーブルのカラムにコメントを追加する
     *
     * @param string $table
     * @param array $comments
     * @return void
     */
    public function addCommentsToColumns(string $table, array $comments): void
    {
        $columnsInfo = DB::table('information_schema.columns')
            ->select(
                // 大文字小文字が統一されていない場合があるので、小文字で統一する
                'column_name as column_name',
                'column_type as column_type',
                'is_nullable as is_nullable',
                'column_default as column_default',
                'extra as extra',
                'character_set_name as character_set_name',
                'collation_name as collation_name')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->whereIn('column_name', array_keys($comments))
            ->get()
            ->keyBy('column_name');

        foreach ($comments as $column => $comment) {
            if (!isset($columnsInfo[$column])) {
                continue;
            }

            $col = $columnsInfo[$column];
            $type = $col->column_type;
            $null = $col->is_nullable === 'NO' ? 'NOT NULL' : 'NULL';
            $default = $col->column_default !== null
                ? "DEFAULT '" . addslashes($col->column_default) . "'"
                : '';
            $extra = $col->extra;

            $charset = !empty($col->character_set_name)
                ? 'CHARACTER SET ' . $col->character_set_name
                : '';

            $collate = !empty($col->collation_name)
                ? 'COLLATE ' . $col->collation_name
                : '';

            // 文字列型にのみ文字セットと照合順序を明示的に付与
            $sql = sprintf(
                "ALTER TABLE `%s` CHANGE `%s` `%s` %s %s %s %s %s %s COMMENT '%s'",
                $table,
                $column,
                $column,
                $type,
                $charset,
                $collate,
                $null,
                $default,
                $extra,
                addslashes($comment)
            );

            // 余分なスペースを削除
            $sql = preg_replace('/\s+/', ' ', trim($sql));

            DB::statement($sql);
        }
    }
}
