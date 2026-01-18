<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明
    // UNIQUE	usr_jump_plus_rewards	id	varchar(255)	FALSE		ID
    // PK(1/2)	usr_jump_plus_rewards	usr_user_id	varchar(255)	FALSE		usr_users.id
    // PK(2/2)	usr_jump_plus_rewards	opr_jump_plus_reward_schedule_id	varchar(255)	FALSE		opr_jump_plus_reward_schedules.id
    //          usr_jump_plus_rewards	created_at	timestamp	FALSE		作成日時のタイムスタンプ
    //          usr_jump_plus_rewards	updated_at	timestamp	FALSE		更新日時のタイムスタンプ

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usr_jump_plus_rewards', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('opr_jump_plus_reward_schedule_id', 255)->comment('opr_jump_plus_reward_schedules.id');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'opr_jump_plus_reward_schedule_id']);

            $table->comment('ジャンプ+連携報酬の受取管理');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_jump_plus_rewards');
    }
};
