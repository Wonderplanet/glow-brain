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
        Schema::create('usr_artwork_parties', function (Blueprint $table) {
            $table->string('id', 255);
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->unsignedInteger('party_no')->comment('パーティ番号');
            $table->string('party_name', 10)->comment('パーティ名');
            $table->string('mst_artwork_id_1', 255)->comment('1スロット目の原画ID');
            $table->string('mst_artwork_id_2', 255)->nullable()->comment('2スロット目の原画ID');
            $table->string('mst_artwork_id_3', 255)->nullable()->comment('3スロット目の原画ID');
            $table->string('mst_artwork_id_4', 255)->nullable()->comment('4スロット目の原画ID');
            $table->string('mst_artwork_id_5', 255)->nullable()->comment('5スロット目の原画ID');
            $table->string('mst_artwork_id_6', 255)->nullable()->comment('6スロット目の原画ID');
            $table->string('mst_artwork_id_7', 255)->nullable()->comment('7スロット目の原画ID');
            $table->string('mst_artwork_id_8', 255)->nullable()->comment('8スロット目の原画ID');
            $table->string('mst_artwork_id_9', 255)->nullable()->comment('9スロット目の原画ID');
            $table->string('mst_artwork_id_10', 255)->nullable()->comment('10スロット目の原画ID');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'party_no']);
            $table->comment('ユーザー原画パーティテーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_artwork_parties');
    }
};
