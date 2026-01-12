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
        Schema::create('mst_white_words', function (Blueprint $table) {

            $table->string('id', 255)->primary()->comment('id');
            $table->string('word', 255)->comment('ホワイトワード');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->comment('NGワードから除外されるホワイトワード設定マスターテーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_white_words');
    }
};
