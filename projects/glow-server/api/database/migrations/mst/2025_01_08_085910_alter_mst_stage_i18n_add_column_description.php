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
        // mst_stage_i18nにtext型のdescriptionカラムを追加
        Schema::table('mst_stages_i18n', function (Blueprint $table) {
            $table->text('description')->after('name')->comment('ステージ情報');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stages_i18n', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
