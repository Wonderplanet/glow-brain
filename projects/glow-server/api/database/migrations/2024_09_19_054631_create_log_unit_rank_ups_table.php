<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明	パラメータ説明
    // PRI	log_unit_rank_ups	id	varchar(255)	FALSE		ULID
	// log_unit_rank_ups	usr_user_id	varchar(255)	FALSE		usr_users.id
	// log_unit_rank_ups	nginx_request_id	varchar(255)	FALSE		APIリクエスト単位でNginxにて生成されるユニークID
	// log_unit_rank_ups	request_id	varchar(255)	FALSE		APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID
	// log_unit_rank_ups	logging_no	int	FALSE		APIリクエスト中でのログの順番
	// log_unit_rank_ups	mst_unit_id	varchar(255)	FALSE		mst_units.id	ランクアップしたユニット
	// log_unit_rank_ups	before_rank	int	FALSE			強化前のランク
	// log_unit_rank_ups	after_rank	int	FALSE			強化後のランク
	// log_unit_rank_ups	created_at	timestamp	TRUE		作成日時のタイムスタンプ
	// log_unit_rank_ups	updated_at	timestamp	TRUE		更新日時のタイムスタンプ

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_unit_rank_ups', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_unit_id', 255)->comment('ランクアップしたユニット(mst_units.id)');
            $table->integer('before_rank')->comment('強化前のランク');
            $table->integer('after_rank')->comment('強化後のランク');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_unit_rank_ups');
    }
};
