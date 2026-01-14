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
        // カラム No	INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明	パラメータ説明
        // 1	PRI	log_gacha_actions	id	varchar(255)	FALSE		ULID
        // 2		log_gacha_actions	usr_user_id	varchar(255)	FALSE		usr_users.id
        // 3		log_gacha_actions	nginx_request_id	varchar(255)	FALSE		APIリクエスト単位でNginxにて生成されるユニークID
        // 4		log_gacha_actions	request_id	varchar(255)	FALSE		APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID
        // 5		log_gacha_actions	logging_no	int	FALSE		APIリクエスト中でのログの順番
        // 6		log_gacha_actions	opr_gacha_id	varchar(255)	FALSE		opr_gachas.id	引いたガシャ
        // 7		log_gacha_actions	cost_type	varchar(255)	FALSE		消費したコスト情報	"Diamond: プリズム消費
        // PaidDiamond: 有償プリズム消費
        // Free: コストなし
        // Item: ガシャチケット消費
        // Ad: 広告視聴"
        // 8		log_gacha_actions	draw_count	int	FALSE		回した数 (排出数)	回した数 (排出数)
        //         log_gacha_actions	max_rarity_upper_count	int	FALSE		ガシャを回す前の最高レア天井のカウント
        //         log_gacha_actions	pickup_upper_count	int	FALSE		ガシャを回す前のピックアップ天井のカウント
        // 11		log_gacha_actions	created_at	timestamp	TRUE		レコード作成日時
        // 12		log_gacha_actions	updated_at	timestamp	TRUE		レコード更新日時

        Schema::create('log_gacha_actions', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('nginx_request_id', 255)->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id', 255)->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->unsignedInteger('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->string('cost_type', 255)->comment('消費したコスト情報');
            $table->unsignedInteger('draw_count')->comment('回した数 (排出数)');
            $table->unsignedInteger('max_rarity_upper_count')->comment('ガシャを回す前の最高レア天井のカウント');
            $table->unsignedInteger('pickup_upper_count')->comment('ガシャを回す前のピックアップ天井のカウント');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_gacha_actions');
    }
};
