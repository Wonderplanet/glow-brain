<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

//     column	データ型	NULL許容	デフォルト値	カラムの説明
// id	varchar(255)	FALSE		ID
// mst_tutorial_id	varchar(255)	FALSE		mst_tutorials.id
// language	enum	FALSE	ja	言語
// sort_order	timestamp	FALSE	0
// title	varchar(255)	FALSE	""	タイトル
// asset_key	varchar(255)	FALSE	""	アセットキー

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('mst_tutorial_tips_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_tutorial_id')->default('')->comment('mst_tutorials.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->integer('sort_order')->default(0)->comment('並び順(昇順)');
            $table->string('title')->default('')->comment('タイトル');
            $table->string('asset_key')->default('')->comment('アセットキー');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('mst_tutorial_tips_i18n');
    }
};
