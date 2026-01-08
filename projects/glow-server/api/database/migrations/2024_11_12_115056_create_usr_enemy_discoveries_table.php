<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明
    // UNIQUE	usr_enemy_discoveries	id	varchar(255)	FALSE
    // PK	usr_enemy_discoveries	usr_user_id	varchar(255)	FALSE		usr_users.id
    // PK	usr_enemy_discoveries	mst_enemy_character_id	varchar(255)	FALSE		mst_enemy_characters.id
    //     usr_enemy_discoveries	created_at	timestamp	FALSE		作成日時のタイムスタンプ
    //     usr_enemy_discoveries	updated_at	timestamp	FALSE		更新日時のタイムスタンプ

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usr_enemy_discoveries', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_enemy_character_id', 255)->comment('mst_enemy_characters.id');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_enemy_character_id']);

            $table->comment('発見した敵キャラ情報');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_enemy_discoveries');
    }
};
