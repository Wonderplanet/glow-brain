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
        $types = [
            'All',
            'Individual',
        ];

        $resourceTypes = [
            'Exp',
            'Coin',
            'FreeDiamond',
            'Item',
            'Emblem',
            'Stamina',
            'Unit',
        ];
        $languages = ['ja'];

        Schema::create('opr_messages', function (Blueprint $table) use ($types) {
            $table->string('id', 255)->primary();
            $table->timestampTz('start_at')->comment('配布開始時日時');
            $table->timestampTz('expired_at')->comment('表示期限日時');
            $table->enum('type', $types);
            $table->timestampTz('account_created_start_at')->comment('全体配布条件とするアカウント作成日時(開始)');
            $table->timestampTz('account_created_end_at')->comment('全体配布条件とするアカウント作成日時(終了)');
            $table->integer('add_expired_days')->comment('ユーザー受け取り日時加算日数');
        });

        Schema::create('opr_message_rewards', function (Blueprint $table) use ($resourceTypes) {
            $table->string('id', 255)->primary();
            $table->string('opr_message_id', 255);
            $table->enum('resource_type', $resourceTypes);
            $table->string('resource_id', 255)->nullable();
            $table->integer('resource_amount')->unsigned()->nullable();
            $table->integer('display_order')->unsigned();

            $table->unique(['opr_message_id', 'display_order'], 'uk_opr_message_id_display_order');
        });

        Schema::create('opr_messages_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('opr_message_id', 255);
            $table->enum('language', ['ja'])->default('ja');
            $table->text('title');
            $table->text('body');
            $table->unique(['opr_message_id', 'language'], 'uk_opr_message_id_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_messages');
        Schema::dropIfExists('opr_message_rewards');
        Schema::dropIfExists('opr_messages_i18n');
    }
};
