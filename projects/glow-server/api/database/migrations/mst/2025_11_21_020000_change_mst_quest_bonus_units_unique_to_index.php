<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_quest_bonus_units', function (Blueprint $table) {
            // unique制約を削除
            $table->dropUnique('mst_quest_id_mst_unit_id_unique');

            // 通常のインデックスを追加（検索効率のため）
            $table->index([
                'mst_quest_id',
                'mst_unit_id'
            ], 'idx_mst_quest_id_mst_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_quest_bonus_units', function (Blueprint $table) {
            // インデックスを削除
            $table->dropIndex('idx_mst_quest_id_mst_unit_id');

            // unique制約を復元
            $table->unique([
                'mst_quest_id',
                'mst_unit_id'
            ], 'mst_quest_id_mst_unit_id_unique');
        });
    }
};
