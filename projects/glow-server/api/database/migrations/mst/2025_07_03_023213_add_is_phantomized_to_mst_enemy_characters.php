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
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->tinyInteger('is_phantomized')
                ->default(0)
                ->comment('プレイアブルキャラの敵化専用表現用')
                ->after('asset_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->dropColumn('is_phantomized');
        });
    }
};
