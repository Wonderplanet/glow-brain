<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_pvp_matching_score_ranges', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->enum('rank_class_type', ['Bronze','Silver','Gold','Platinum'])->comment('クラスランク');
            $table->integer('rank_class_level')->comment('クラスランクレベル');
            $table->integer('higher_rank_score_upper_range')->comment('格上スコア足し込み上限');
            $table->integer('higher_rank_score_lower_range')->comment('格上スコア足し込み下限');
            $table->integer('same_rank_score_upper_range')->comment('同格スコア足し込み上限');
            $table->integer('same_rank_score_lower_range')->comment('同格スコア足し込み下限'); 
            $table->integer('lower_rank_score_upper_range')->comment('格下スコア足し込み上限');           
            $table->integer('lower_rank_score_lower_range')->comment('格下スコア足し込み下限');

            $table->comment('マッチングに使用するスコアの幅設定情報のマスターテーブル'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_pvp_matching_score_ranges');
    }
};
