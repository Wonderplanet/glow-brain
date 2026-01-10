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
        Schema::dropIfExists('opr_message_rewards');
        Schema::dropIfExists('opr_messages_i18n');
        Schema::dropIfExists('opr_messages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('opr_messages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->timestamp('start_at')->comment('配布開始時日時');
            $table->timestamp('expired_at')->comment('表示期限日時');
            $table->enum('type', ['All', 'Individual']);
            $table->timestamp('account_created_start_at')->nullable()->comment('全体配布条件とするアカウント作成日時(開始)');
            $table->timestamp('account_created_end_at')->nullable()->comment('全体配布条件とするアカウント作成日時(終了)');
            $table->integer('add_expired_days')->comment('ユーザー受け取り日時加算日数');
        });

        Schema::create('opr_messages_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('opr_message_id');
            $table->enum('language', ['ja'])->default('ja');
            $table->text('title');
            $table->text('body');
            $table->unique(['opr_message_id', 'language'], 'uk_opr_message_id_language');
        });

        Schema::create('opr_message_rewards', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('opr_message_id');
            $table->enum('resource_type', ['Exp', 'Coin', 'FreeDiamond', 'Item', 'Emblem', 'Stamina', 'Unit']);
            $table->string('resource_id')->nullable();
            $table->unsignedInteger('resource_amount')->nullable();
            $table->unsignedInteger('display_order');
            $table->unique(['opr_message_id', 'display_order'], 'uk_opr_message_id_display_order');
        });
    }
};
