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
        Schema::create('adm_datalake_logs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('date')->unique()->comment('年月日(YYYYMMDD)');
            $table->tinyInteger('status')->comment('0:未実行 1:mstDB転送完了 2:usrDB転送完了 3:logDB転送完了 4:完了');
            $table->tinyInteger('is_transfer')->comment('0:停止中 1:転送中');
            $table->tinyInteger('try_count')->default(0)->comment('転送試行回数');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_datalake_logs');
    }
};
