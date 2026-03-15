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
        Schema::create('mst_home_koma_patterns', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('asset_key')->comment('アセットキー');
            $table->comment('ホーム表示コマにおける配置パターン設定テーブル');

        });

        Schema::create('mst_home_koma_patterns_i18n', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('mst_home_koma_pattern_id')->comment('mst_home_koma_patterns.id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('name')->comment('コマパターン名');
            $table->comment('ホーム表示コマにおける配置パターンの多言語テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_home_koma_patterns');
        Schema::dropIfExists('mst_home_koma_patterns_i18n');
    }
};
