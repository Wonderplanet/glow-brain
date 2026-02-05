<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait MigrationAddTableCommentTrait
{
    /**
     * テーブルにコメントを追加する
     *
     * @param string $table テーブル名
     * @param string $comment コメント
     * @return void
     */
    public function addCommentToTable(string $table, string $comment): void
    {
        $sql = sprintf(
            "ALTER TABLE `%s` COMMENT = '%s'",
            $table,
            addslashes($comment)
        );
        
        DB::statement($sql);
    }
}
