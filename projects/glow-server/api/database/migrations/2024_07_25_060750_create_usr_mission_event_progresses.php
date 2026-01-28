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
        Schema::create('usr_mission_event_progresses', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->bigInteger('progress')->default(0)->comment('デイリー累積進捗値');
            $table->timestampsTz();
            $table->unique(['usr_user_id', 'criterion_key'], 'usr_user_id_criterion_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_mission_event_progresses');
    }
};
