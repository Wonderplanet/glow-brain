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
        Schema::create('usr_cheat_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('content_type', 255)->comment('コンテンツのタイプ');
            $table->string('target_id', 255)->nullable()->comment('降臨バトルの場合はmst_advent_battles.id');
            $table->json('party_status')->nullable()->comment('パーティステータス');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_cheat_sessions');
    }
};
