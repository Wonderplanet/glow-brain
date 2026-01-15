<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

// CREATE TABLE `opr_jump_plus_reward_schedules` (
//   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
//   `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_jump_plus_rewards.group_id',
//   `start_at` timestamp NOT NULL COMMENT '開始日時',
//   `end_at` timestamp NOT NULL COMMENT '終了日時',
//   `created_at` timestamp NULL DEFAULT NULL,
//   `updated_at` timestamp NULL DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ジャンプ+連携報酬のスケジュール';

// CREATE TABLE `opr_jump_plus_rewards` (
//   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
//   `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '報酬のグルーピングID',
//   `resource_type` enum('FreeDiamond','Coin','Item','Unit','Emblem') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
//   `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬ID',
//   `resource_amount` int NOT NULL COMMENT '報酬の個数',
//   `created_at` timestamp NULL DEFAULT NULL,
//   `updated_at` timestamp NULL DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ジャンプ+連携報酬設定';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete the opr_jump_plus tables
        Schema::dropIfExists('opr_jump_plus_reward_schedules');
        Schema::dropIfExists('opr_jump_plus_rewards');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('opr_jump_plus_reward_schedules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('group_id')->comment('opr_jump_plus_rewards.group_id');
            $table->timestampTz('start_at')->comment('開始日時');
            $table->timestampTz('end_at')->comment('終了日時');
            $table->timestampsTz();

            $table->comment('ジャンプ+連携報酬のスケジュール');
        });

        Schema::create('opr_jump_plus_rewards', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('group_id')->comment('報酬のグルーピングID');
            $table->enum('resource_type', ['FreeDiamond', 'Coin', 'Item', 'Unit', 'Emblem'])->comment('報酬タイプ');
            $table->string('resource_id')->nullable(true)->comment('報酬ID');
            $table->integer('resource_amount')->comment('報酬の個数');
            $table->timestampsTz();

            $table->comment('ジャンプ+連携報酬設定');
        });
    }
};
