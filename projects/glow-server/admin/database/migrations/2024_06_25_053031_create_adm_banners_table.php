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
        Schema::create('adm_in_game_notices', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('opr_in_game_notice_id', 255)->comment('opr_in_game_notices.id');
            $table->enum('status', ['InProgress', 'PendingApproval', 'Reject', 'Approved', 'Active', 'Withdrawn'])->comment('ステータス');
            $table->bigInteger('author_adm_user_id')->comment('作成者ユーザーID');
            $table->bigInteger('approval_adm_user_id')->nullable()->comment('承認者ユーザーID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_in_game_notices');
    }
};
