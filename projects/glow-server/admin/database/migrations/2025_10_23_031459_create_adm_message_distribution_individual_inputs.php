<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $status = [
            'Editing',
            'Pending',
            'Approved',
        ];

        $targetTypes = [
            'UserId',
            'MyId',
        ];

        $targetIdInputTypes = [
            'Input',
            'Csv',
        ];

        $createdTypes = [
            'Unset',
            'Started',
            'Ended',
            'Both',
        ];

        Schema::create('adm_message_distribution_individual_inputs', function (Blueprint $table) use ($status, $targetTypes, $targetIdInputTypes, $createdTypes) {
            $table->id()->comment('ID');
            $table->enum('create_status', $status)->comment('作成ステータス');
            $table->text('title')->comment('タイトル');
            $table->timestampTz('start_at')->comment('開始日時');
            $table->timestampTz('expired_at')->nullable()->comment('終了日時');
            $table->string('mng_message_id', 255)->nullable()->comment('mng_messages.id');
            $table->text('mng_messages_txt')->comment('mng_messagesレコードのシリアライズデータ');
            $table->text('mng_message_distributions_txt')->comment('mng_message_rewardsレコードのシリアライズデータ');
            $table->text('mng_message_i18ns_txt')->comment('mng_messages_i18nレコードのシリアライズデータ');
            $table->enum('target_type', $targetTypes);
            $table->text('target_ids_txt')->nullable()->comment('対象IDリストテキスト');
            $table->enum('display_target_id_input_type', $targetIdInputTypes)->comment('対象IDリストの入力タイプ');
            $table->enum('account_created_type', $createdTypes)->comment('アカウント生成タイプ');
            $table->string('adm_promotion_tag_id')->nullable()->comment('昇格タグID');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_message_distribution_individual_inputs');
    }
};
