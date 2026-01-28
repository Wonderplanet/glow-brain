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
        Schema::create('opr_in_game_notices', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->enum('display_type', ['BasicBanner', 'Dialog'])->comment('表示モード');
            $table->string('mst_client_path_id', 255)->nullable()->comment('mst_client_paths.id');
            $table->unsignedTinyInteger('enable')->comment('有効フラグ');
            $table->unsignedInteger('priority')->comment('表示優先度');
            $table->enum('display_frequency_type', ['Always', 'Daily', 'Weekly', 'Monthly', 'Once'])->comment('表示頻度タイプ');
            $table->timestampTz('start_at')->comment('掲載開始日時');
            $table->timestampTz('end_at')->comment('掲載終了日時');
            $table->timestamps();
        });

        Schema::create('opr_in_game_notices_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('opr_in_game_notice_id', 255)->comment('opr_in_game_notices.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->text('title')->nullable()->comment('タイトル');
            $table->text('description')->comment('本文テキスト');
            $table->string('banner_url', 255)->comment('バナーURL');
            $table->timestamps();
        });

        Schema::create('mst_client_paths', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key');
            $table->enum('type', ['InGame', 'Web'])->comment('遷移先タイプ');
            $table->string('path', 255)->comment('遷移先情報');
            $table->string('path_detail', 255)->comment('遷移先詳細情報');
            $table->timestamps();
        });

        Schema::create('mst_client_paths_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key');
            $table->string('mst_client_path_id', 255)->comment('mst_client_paths.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('button_title', 255)->comment('ボタンに表示するテキスト');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_in_game_notices');
        Schema::dropIfExists('opr_in_game_notices_i18n');
        Schema::dropIfExists('mst_client_paths');
        Schema::dropIfExists('mst_client_paths_i18n');
    }
};
