<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * TTLを設定するテーブルの配列
     * 各テーブルに対して31日のTTLを設定
     */
    private array $tables = [
        'log_artwork_grade_ups',
    ];

    public function up(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        foreach ($this->tables as $table) {
            // created_atから31日でTTL設定
            DB::statement("ALTER TABLE `{$table}` TTL = `created_at` + INTERVAL 31 DAY");
            // 1日1回にする
            DB::statement("ALTER TABLE `{$table}` TTL_JOB_INTERVAL = '24h'");
            // TTLを有効化 (デフォルトONだが念のため)
            DB::statement("ALTER TABLE `{$table}` TTL_ENABLE = 'ON'");
        }
    }

    public function down(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        foreach ($this->tables as $table) {
            // TTL属性を削除
            DB::statement("ALTER TABLE `{$table}` REMOVE TTL");
        }
    }

    private function shouldRun(): bool
    {
        $result = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'information_schema' AND table_name = 'cluster_info'");
        return $result[0]->count > 0;
    }
};
