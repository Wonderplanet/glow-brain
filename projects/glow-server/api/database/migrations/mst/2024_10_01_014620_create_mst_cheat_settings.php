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
        Schema::create('mst_cheat_settings', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->enum('content_type', ['AdventBattle', 'Pvp'])->comment('コンテンツのタイプ');
            $table->enum('cheat_type', ['BattleTime', 'MaxDamage', 'BattleStatusMismatch', 'MasterDataStatusMismatch'])->comment('チートタイプ');
            $table->integer('cheat_value')->comment('チートとする値');
            $table->tinyInteger('is_excluded_ranking')->unsigned()->default(0)->comment('チート検出時に即ランキング除外するか');
            $table->timestampTz('start_at')->comment('チート設定開始日');
            $table->timestampTz('end_at')->comment('チート設定終了日');
            $table->bigInteger('release_key')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_cheat_settings');
    }
};
