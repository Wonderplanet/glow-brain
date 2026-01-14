<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 新規テーブル追加
//     INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明
// PRI	usr_stage_enhances	usr_user_id	varchar(255)	FALSE		usr_users.id
// PRI	usr_stage_enhances	mst_stage_id	varchar(255)	FALSE		mst_stages.id
// 	usr_stage_enhances	clear_count	unsigned int	FALSE	0	過去通算のクリア回数
// 	usr_stage_enhances	reset_challenge_count	unsigned int	FALSE	0	リセット以降の通常の挑戦回数
// 	usr_stage_enhances	reset_ad_challenge_count	unsigned int	FALSE	0	リセット以降の広告視聴による挑戦回数
// 	usr_stage_enhances	max_score	unsigned bigint	FALSE	0	過去通算のスコア最大値
// 	usr_stage_enhances	latest_reset_at	timestamp	FALSE		リセット日時
// 	usr_stage_enhances	created_at	timestamp	FALSE		作成日時のタイムスタンプ
// 	usr_stage_enhances	updated_at	timestamp	FALSE		更新日時のタイムスタンプ

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usr_stage_enhances', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_stage_id', 255)->comment('mst_stages.id');
            $table->unsignedInteger('clear_count')->default(0)->comment('過去通算のクリア回数');
            $table->unsignedInteger('reset_challenge_count')->default(0)->comment('リセット以降の通常の挑戦回数');
            $table->unsignedInteger('reset_ad_challenge_count')->default(0)->comment('リセット以降の広告視聴による挑戦回数');
            $table->unsignedBigInteger('max_score')->default(0)->comment('過去通算のスコア最大値');
            $table->timestampTz('latest_reset_at')->nullable()->comment('リセット日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mst_stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_stage_enhances');
    }
};
