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
        Schema::create('opr_campaigns', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('campaign_type', ['Stamina', 'Exp', 'ArtworkFragment', 'ItemDrop', 'CoinDrop', 'ChallengeCount'])->comment('キャンペーンタイプ');
            $table->enum('target_type', ['NormalQuest', 'EnhanceQuest', 'EventQuest', 'PvP', 'DescentBattle'])->comment('キャンペーン対象タイプ');
            $table->enum('difficulty', ['Normal', 'Hard', 'VeryHard'])->nullable()->comment('難易度');
            $table->enum('target_id_type', ['Quest', 'Series'])->nullable()->comment('指定するIDのタイプ');
            $table->string('target_id')->nullable()->comment('mst_quests.idかmst_series.id');
            $table->unsignedSmallInteger('effect_value')->comment('効果値');
            $table->string('asset_key')->nullable()->comment('対象となるクエストID');
            $table->timestampTz('start_at')->comment('キャンペーン開始日時');
            $table->timestampTz('end_at')->comment('キャンペーン終了日時');
            $table->timestamps();
        });

        Schema::create('opr_campaigns_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('opr_campaign_id')->comment('opr_campaigns.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('description')->comment('詳細');
            $table->timestamps();
            $table->unique(['opr_campaign_id', 'language'], 'uk_opr_campaign_id_language');
        });

        Schema::table('mst_quests', function (Blueprint $table) {
            $table->string('mst_series_id')->default('')->comment('mst_series.id')->after('mst_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_campaigns');
        Schema::dropIfExists('opr_campaigns_i18n');
        Schema::table('mst_quests', function (Blueprint $table) {
            $table->dropColumn('mst_series_id');
        });
    }
};
