<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usr_comeback_bonus_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('mst_comeback_bonus_schedule_id')->comment('mst_comeback_bonus_schedules.id');
            $table->unsignedInteger('start_count')->default(1)->comment('開始回数');
            $table->integer('progress')->unsigned()->comment('ログイン回数進捗');
            $table->timestampTz('latest_update_at')->nullable()->comment('ログイン更新日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_comeback_bonus_schedule_id'], 'pk_usr_user_id_comeback_bonus_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_comeback_bonus_progresses');
    }
};
