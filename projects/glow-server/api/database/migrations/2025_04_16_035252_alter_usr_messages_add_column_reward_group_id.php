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
        //
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->string('reward_group_id', 255)->nullable()->default(null)->comment('リワードグループID')->after('message_source');
            $table->index(['usr_user_id', 'reward_group_id'], 'user_id_reward_group_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('usr_messages', function (Blueprint $table) {
            $table->dropIndex('user_id_reward_group_id_index');
            $table->dropColumn('reward_group_id');
        });
    }
};
