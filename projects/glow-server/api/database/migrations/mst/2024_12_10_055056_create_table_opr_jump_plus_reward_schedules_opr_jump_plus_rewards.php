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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('opr_jump_plus_reward_schedules');
        Schema::dropIfExists('opr_jump_plus_rewards');
    }
};
