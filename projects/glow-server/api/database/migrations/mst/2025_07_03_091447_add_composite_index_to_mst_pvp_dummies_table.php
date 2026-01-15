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
        Schema::table('mst_pvp_dummies', function (Blueprint $table) {
            // 既存のscoreインデックスを削除
            $table->dropIndex(['score']);
            
            // rank_class_typeとrank_class_levelの複合インデックスを追加
            $table->index(['rank_class_type', 'rank_class_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_pvp_dummies', function (Blueprint $table) {
            // 複合インデックスを削除
            $table->dropIndex(['rank_class_type', 'rank_class_level']);
            
            // scoreインデックスを復元
            $table->index('score');
        });
    }
};
