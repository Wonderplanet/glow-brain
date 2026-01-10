<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // カラム No	INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明
    // 1	PRI	mst_home_banners	id	varchar(255)	FALSE		ID
    // 2		mst_home_banners	release_key	int	FALSE	1
    // 3		mst_home_banners	destination	enum	FALSE		タップ時の遷移先タイプ
    // 4		mst_home_banners	destinationPath	text	FALSE		タップ時の遷移先における情報
    // 5		mst_home_banners	bannerPath	varchar(255)	FALSE		表示するバナーのパス
    // 6		mst_home_banners	startAt	timestamp	FALSE		掲載開始日時
    // 7		mst_home_banners	endAt	timestamp	FALSE		掲載終了日時
    // 8		mst_home_banners	sortOrder	int	FALSE		ホームで出す表示順番

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('mst_home_banners', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->enum('destination', ['None', 'Gacha', 'CreditShop', 'BasicShop', 'Event', 'Web'])->default('None')->comment('タップ時の遷移先タイプ');
            $table->string('destination_path')->default('')->comment('タップ時の遷移先における情報');
            $table->string('banner_path')->default('')->comment('表示するバナーのパス');
            $table->integer('sort_order')->comment('ホームで出す表示順番');
            $table->timestampTz('start_at')->comment('掲載開始日時');
            $table->timestampTz('end_at')->comment('掲載終了日時');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('mst_home_banners');
    }
};
