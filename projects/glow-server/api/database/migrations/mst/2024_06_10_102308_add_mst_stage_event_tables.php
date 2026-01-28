<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_events', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_series_id')->comment('作品ID');
            $table->tinyInteger('is_displayed_series_logo')->default(0)->comment('作品ロゴの表示有無');
            $table->tinyInteger('is_displayed_jump_plus')->default(0)->comment('作品を読むボタンの表示有無');
            $table->timestamp('start_at')->comment('開始日時');
            $table->timestamp('end_at')->comment('終了日時');
            $table->string('asset_key');
            $table->bigInteger('release_key');
            $table->index('mst_series_id', 'mst_series_id_index');
        });

        Schema::create('mst_events_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_event_id');
            $table->enum('language', ['ja']);
            $table->string('name')->comment('イベント名');
            $table->string('balloon')->comment('吹き出し内テキスト');
            $table->bigInteger('release_key');
            $table->unique(['mst_event_id', 'language'], 'uk_mst_event_id_language');
        });

        Schema::create('mst_stage_event_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_stage_id')->comment('mst_stages.id');
            $table->enum('reset_type', ['Daily'])->nullable()->comment('リセットタイプ');
            $table->integer('clearable_count')->nullable()->comment('クリア可能回数');
            $table->integer('ad_challenge_count')->default(0)->comment('広告視聴で挑戦できる回数');
            $table->timestamp('start_at')->comment('開始日時');
            $table->timestamp('end_at')->comment('終了日時');
            $table->bigInteger('release_key');
            $table->unique(['mst_stage_id'], 'uk_mst_stage_id');
        });

        DB::statement("ALTER TABLE mst_quests MODIFY COLUMN quest_type ENUM ('Normal', 'Event') NOT NULL COMMENT 'クエストの種類' AFTER `id`");

        Schema::table('mst_quests', function (Blueprint $table) {
            $table->string('mst_event_id')
                ->nullable()
                ->comment('mst_events.id')
                ->after('quest_type');
            $table->index(['mst_event_id'], 'idx_mst_event_id');
            $table->index(['quest_type'], 'idx_quest_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_events');
        Schema::dropIfExists('mst_events_i18n');
        Schema::dropIfExists('mst_stage_event_settings');

        Schema::table('mst_quests', function (Blueprint $table) {
            $table->dropIndex('idx_quest_type');
            $table->dropIndex('idx_mst_event_id');
            $table->dropColumn('mst_event_id');
        });
        DB::statement("ALTER TABLE mst_quests MODIFY COLUMN quest_type VARCHAR(255) NOT NULL");
    }
};
