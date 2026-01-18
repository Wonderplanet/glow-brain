<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('sys_pvp_seasons');
        Schema::create('sys_pvp_seasons', function (Blueprint $table) {
            $table->string('id', 16)->primary();
            $table->string('mst_pvp_id', 16);
            $table->timestampTz('start_at')->comment('シーズン開始日時');
            $table->timestampTz('end_at')->comment('シーズン終了日時');
            $table->timestampTz('closed_at')->nullable()->comment('シーズン終了後のクローズ日時');

            $table->timestampsTz();

            $table->index('mst_pvp_id');
        });

        // リリース最初のシーズンデータは開始時刻が違う為ここで追加しておく
        DB::table('sys_pvp_seasons')->insert([
            'id' => '2025039',
            'mst_pvp_id' => '2025039',
            'start_at' => '2025-09-22 2:00:00',
            'end_at' => '2025-09-28 14:59:59',
            'closed_at' => '2025-09-29 2:59:59',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_pvp_seasons');
        Schema::create('sys_pvp_seasons', function (Blueprint $table) {
            $table->string('id', 16)->unique();
            $table->string('mst_pvp_id', 16);
            $table->dateTime('start_at')->comment('シーズン開始日時');
            $table->dateTime('end_at')->comment('シーズン終了日時');
            $table->dateTime('closed_at')->nullable()->comment('シーズン終了後のクローズ日時');
        });
    }
};
