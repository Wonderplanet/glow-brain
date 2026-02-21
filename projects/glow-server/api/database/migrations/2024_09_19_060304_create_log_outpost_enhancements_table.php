<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明	パラメータ説明
    // PRI	log_outpost_enhancements	id	varchar(255)	FALSE		ULID
	// log_outpost_enhancements	usr_user_id	varchar(255)	FALSE		usr_users.id
	// log_outpost_enhancements	nginx_request_id	varchar(255)	FALSE		APIリクエスト単位でNginxにて生成されるユニークID
	// log_outpost_enhancements	request_id	varchar(255)	FALSE		APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID
	// log_outpost_enhancements	logging_no	int	FALSE		APIリクエスト中でのログの順番
	// log_outpost_enhancements	mst_outpost_enhancement_id	varchar(255)	FALSE		mst_outpost_enhancements.id	強化したゲートの強化項目
	// log_outpost_enhancements	before_level	int	FALSE			強化前のレベル
	// log_outpost_enhancements	after_level	int	FALSE			強化後のレベル
	// log_outpost_enhancements	created_at	timestamp	TRUE		作成日時のタイムスタンプ
	// log_outpost_enhancements	updated_at	timestamp	TRUE		更新日時のタイムスタンプ

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_outpost_enhancements', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_outpost_enhancement_id', 255)->comment('mst_outpost_enhancements.id 強化したゲートの強化項目');
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
        Schema::dropIfExists('log_outpost_enhancements');
    }
};
