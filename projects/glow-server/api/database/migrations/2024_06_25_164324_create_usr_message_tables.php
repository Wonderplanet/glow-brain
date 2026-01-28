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
        Schema::create('usr_messages', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index('usr_user_id_index');
            $table->string('opr_message_id', 255)->nullable();
            $table->string('message_source', 255)->comment('メッセージの送信元')->nullable();
            $table->timestampTz('opened_at')->comment('読んだ日付')->nullable();
            $table->timestampTz('received_at')->comment('報酬を受け取った日付')->nullable();
            $table->timestampTz('expired_at')->comment('受け取り期限日時 nullの場合はなしとして扱う')->nullable();
            $table->timestampsTz();

            $table->index(['usr_user_id', 'expired_at'], 'usr_user_id_expired_at_index');
        });

        Schema::create('usr_temporary_individual_messages', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255);
            $table->string('opr_message_id', 255);
            $table->timestampsTz();

            $table->unique(['usr_user_id', 'opr_message_id'], 'usr_user_id_opr_message_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_messages');
        Schema::dropIfExists('usr_temporary_individual_messages');
    }
};
