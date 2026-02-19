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
        Schema::create('usr_tutorial_gachas', function (Blueprint $table) {
            $table->string('id', 255)->unique()->comment('ID');
            $table->string('usr_user_id', 255)->primary()->comment('usr_users.id');
            $table->json('gacha_result_json')->comment('ガシャ結果の一時保存json');
            $table->timestamp('confirmed_at')->nullable()->comment('ガシャ結果を確定した日時');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_tutorial_gachas');
    }
};
