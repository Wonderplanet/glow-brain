<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

//     INDEX	table	column	データ型	NULL許容	デフォルト値	カラムの説明	パラメータ説明
// PRIMARY	mst_party_unit_counts	id	varchar(255)	FALSE
// UNIQUE1	mst_party_unit_counts	mst_stage_id	varchar(255)	FALSE		mst_stages.id（メインクエストのみ）	"メインクエストのステージID
// 進捗しているステージによって開放されるキャラ数が増える"
// 	mst_party_unit_counts	max_count	unsigned int	FALSE		設定可能なパーティキャラ数
// 	mst_party_unit_counts	release_key	bigint	FALSE		リリースキー

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_party_unit_counts', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('mst_stage_id', 255)->unique()->comment('mst_stages.id。進捗しているステージ');
            $table->unsignedInteger('max_count')->comment('設定可能なパーティキャラ数');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_party_unit_counts');
    }
};
