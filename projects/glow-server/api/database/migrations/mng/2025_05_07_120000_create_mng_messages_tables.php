<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = Database::MNG_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mng_message_rewards', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mng_message_id')->comment('mng_messages.id');
            $table->enum('resource_type', ['Exp', 'Coin', 'FreeDiamond', 'Item', 'Emblem', 'Stamina', 'Unit'])->comment('リソースタイプ');
            $table->string('resource_id')->nullable()->comment('リソースID');
            $table->unsignedInteger('resource_amount')->nullable()->comment('リソース数量');
            $table->unsignedInteger('display_order')->comment('表示順');
            $table->unique(['mng_message_id', 'display_order'], 'uk_mng_message_id_display_order');
            $table->comment('メッセージ報酬テーブル');
        });

        Schema::create('mng_messages', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->timestamp('start_at')->comment('配布開始時日時');
            $table->timestamp('expired_at')->comment('表示期限日時');
            $table->enum('type', ['All', 'Individual'])->comment('配布種別');
            $table->timestamp('account_created_start_at')->nullable()->comment('全体配布条件とするアカウント作成日時(開始)');
            $table->timestamp('account_created_end_at')->nullable()->comment('全体配布条件とするアカウント作成日時(終了)');
            $table->integer('add_expired_days')->comment('ユーザー受け取り日時加算日数');
            $table->comment('メッセージ管理テーブル');
        });

        Schema::create('mng_messages_i18n', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mng_message_id')->comment('mng_messages.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->text('title')->comment('タイトル');
            $table->text('body')->comment('本文');
            $table->unique(['mng_message_id', 'language'], 'uk_mng_message_id_language');
            $table->comment('メッセージ多言語テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mng_message_rewards');
        Schema::dropIfExists('mng_messages_i18n');
        Schema::dropIfExists('mng_messages');
    }
};
