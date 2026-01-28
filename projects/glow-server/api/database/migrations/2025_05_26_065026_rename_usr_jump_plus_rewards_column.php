<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // CREATE TABLE `usr_jump_plus_rewards` (
//   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
//   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
//   `opr_jump_plus_reward_schedule_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_jump_plus_reward_schedules.id',
//   `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
//   `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
//   PRIMARY KEY (`usr_user_id`,`opr_jump_plus_reward_schedule_id`) /*T![clustered_index] CLUSTERED */,
//   UNIQUE KEY `usr_jump_plus_rewards_id_unique` (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ジャンプ+連携報酬の受取管理';

// opr_jump_plus_reward_schedule_idをmng_jump_plus_reward_schedule_idに改名し、列コメントも修正
// 上記を対応するために、テーブルがあれば削除して、create table しなおす

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete the usr_jump_plus_rewards table if it exists
        Schema::dropIfExists('usr_jump_plus_rewards');

        // Create the usr_jump_plus_rewards table
        Schema::create('usr_jump_plus_rewards', function (Blueprint $table) {
            $table->string('id', 255)->comment('UUID')->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mng_jump_plus_reward_schedule_id', 255)->comment('mng_jump_plus_reward_schedules.id');
            $table->timestampsTz();
            $table->primary(['usr_user_id', 'mng_jump_plus_reward_schedule_id']);
            $table->comment('ジャンプ+連携報酬の受取管理');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the usr_jump_plus_rewards table if it exists
        Schema::dropIfExists('usr_jump_plus_rewards');

        // Recreate the usr_jump_plus_rewards table with the original column name
        Schema::create('usr_jump_plus_rewards', function (Blueprint $table) {
            $table->string('id', 255)->comment('UUID')->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('opr_jump_plus_reward_schedule_id', 255)->comment('opr_jump_plus_reward_schedules.id');
            $table->timestampsTz();
            $table->primary(['usr_user_id', 'opr_jump_plus_reward_schedule_id']);
            $table->comment('ジャンプ+連携報酬の受取管理');
        });
    }
};
