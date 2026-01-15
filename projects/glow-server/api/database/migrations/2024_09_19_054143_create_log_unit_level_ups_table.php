<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明	パラメータ説明
    // PRI	log_unit_level_ups	id	varchar(255)	FALSE		ULID
	// log_unit_level_ups	usr_user_id	varchar(255)	FALSE		usr_users.id
	// log_unit_level_ups	nginx_request_id	varchar(255)	FALSE		APIリクエスト単位でNginxにて生成されるユニークID
	// log_unit_level_ups	request_id	varchar(255)	FALSE		APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID
	// log_unit_level_ups	logging_no	int	FALSE		APIリクエスト中でのログの順番
	// log_unit_level_ups	mst_unit_id	varchar(255)	FALSE		mst_units.id	レベルアップしたユニット
	// log_unit_level_ups	before_level	int	FALSE			強化前のレベル
	// log_unit_level_ups	after_level	int	FALSE			強化後のレベル
	// log_unit_level_ups	created_at	timestamp	TRUE		作成日時のタイムスタンプ
	// log_unit_level_ups	updated_at	timestamp	TRUE		更新日時のタイムスタンプ

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_unit_level_ups', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_unit_id', 255)->comment('レベルアップしたユニット(mst_units.id)');
            $table->integer('before_level')->comment('強化前のレベル');
            $table->integer('after_level')->comment('強化後のレベル');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_unit_level_ups');
    }
};
