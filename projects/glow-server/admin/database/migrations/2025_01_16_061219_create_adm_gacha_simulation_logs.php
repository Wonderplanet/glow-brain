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
        Schema::create('adm_gacha_simulation_logs', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('adm_user_id')->comment('操作を行った対応者のuser_id');
            $table->string('opr_gacha_id')->unique()->comment('実行したガシャID');
            $table->bigInteger('simulation_num')->comment('シミュレーション時に設定した回数');
            $table->string('mst_gacha_data_hash')->nullable()->comment('アップロードしたガシャマスタデータのハッシュ値');
            $table->json('simulation_data')->nullable()->comment('シミュレーション結果データ');
            $table->unsignedSmallInteger('report_status')->comment('検証結果の送信ステータス 0:送信前 1:送信済');
            $table->timestampTz('simulated_at')->comment('シミュレーション日時');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_gacha_simulation_logs');
    }
};

