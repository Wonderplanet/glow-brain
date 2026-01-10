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
        Schema::create('sys_pvp_seasons', function (Blueprint $table) {
            $table->string('id', 16)->unique();
            $table->string('mst_pvp_id', 16);
            $table->dateTime('start_at')->comment('シーズン開始日時');
            $table->dateTime('end_at')->comment('シーズン終了日時');
            $table->dateTime('closed_at')->nullable()->comment('シーズン終了後のクローズ日時');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_pvp_seasons');
    }
};
