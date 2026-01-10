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
        Schema::table('mst_stages_i18n', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        Schema::table('mst_in_games_i18n', function (Blueprint $table) {
            $table->text('description')->after('result_tips')->comment('ステージ情報');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stages_i18n', function (Blueprint $table) {
            $table->text('description')->after('name')->comment('ステージ情報');
        });
        Schema::table('mst_in_games_i18n', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
