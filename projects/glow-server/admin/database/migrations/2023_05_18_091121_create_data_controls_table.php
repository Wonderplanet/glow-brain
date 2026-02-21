<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 汎用データロック用テーブル
        Schema::create('data_controls', function (Blueprint $table) {
            $table->id();
            $table->string('control_type')->unique(); // ロックの識別子
            $table->bigInteger('version')->default(0); // 楽観ロック用
            $table->string('status', 128);
            $table->text('data')->nullable(); // ロックに際して何かあれば
            $table->dateTime('deleted_at')->nullable()->default(null);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_controls');
    }
};
