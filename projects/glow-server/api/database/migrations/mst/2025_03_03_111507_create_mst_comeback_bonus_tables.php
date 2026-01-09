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
        Schema::create('mst_comeback_bonus_schedules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('inactive_condition_days')->comment('未ログイン期間の条件日数');
            $table->timestampTz('start_at')->comment('開始日時');
            $table->timestampTz('end_at')->comment('終了日時');
            $table->bigInteger('release_key')->default(1);
        });

        Schema::create('mst_comeback_bonuses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_comeback_bonus_schedule_id')->comment('mst_comeback_bonus_schedules.id');
            $table->unsignedInteger('login_day_count')->nullable(false)->comment('条件とするログイン日数');
            $table->string('mst_daily_bonus_reward_group_id')->comment('mst_daily_bonus_reward.group_id');
            $table->unsignedInteger('sort_order')->default(0)->comment('表示順');
            $table->bigInteger('release_key')->default(1);

            $table->unique(['mst_comeback_bonus_schedule_id', 'login_day_count'], 'uk_schedule_id_login_day_count');
        });

        $resourceTypes = [
            'Exp',
            'Coin',
            'FreeDiamond',
            'Item',
            'Emblem',
            'Stamina',
            'Unit',
        ];
        Schema::create('mst_daily_bonus_rewards', function (Blueprint $table) use ($resourceTypes) {
            $table->string('id')->primary();
            $table->string('group_id', 255)->comment('報酬グルーピングID');
            $table->enum('resource_type', $resourceTypes)->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable()->comment('報酬ID');
            $table->integer('resource_amount')->unsigned()->comment('報酬数量');
            $table->bigInteger('release_key')->default(1);
            $table->index('group_id', 'idx_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_daily_bonus_rewards');
        Schema::dropIfExists('mst_comeback_bonuses');
        Schema::dropIfExists('mst_comeback_bonus_schedules');
    }
};
