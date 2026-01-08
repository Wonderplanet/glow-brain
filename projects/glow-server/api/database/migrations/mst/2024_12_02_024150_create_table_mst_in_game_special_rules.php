<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_stage_event_rules` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'グループ',
    //     `rule_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ルール条件タイプ',
    //     `rule_value` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ルール条件値',
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_in_game_special_rules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->enum('content_type', ['Stage', 'AdventBattle'])->comment('インゲームコンテンツタイプ');
            $table->string('target_id')->comment('各インゲームコンテンツごとの対象マスタテーブルのID');
            $table->string('rule_type')->nullable(false)->comment('ルール条件タイプ');
            $table->string('rule_value')->nullable()->comment('ルール条件値');
            $table->timestampTz('start_at')->nullable(false)->comment('開始日時');
            $table->timestampTz('end_at')->nullable(false)->comment('終了日時');
        });

        // mst_stage_event_rulesテーブル削除
        Schema::dropIfExists('mst_stage_event_rules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_in_game_special_rules');

        Schema::create('mst_stage_event_rules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('group_id')->nullable(false)->comment('グループ');
            $table->string('rule_type')->nullable(false)->comment('ルール条件タイプ');
            $table->string('rule_value')->nullable()->comment('ルール条件値');
            $table->bigInteger('release_key')->nullable(false)->default(1);
        });
    }
};
