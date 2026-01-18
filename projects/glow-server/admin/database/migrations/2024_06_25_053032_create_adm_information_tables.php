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
        Schema::create('adm_informations', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->unsignedTinyInteger('enable');
            $table->enum('status', ['InProgress', 'PendingApproval', 'Reject', 'Approved', 'Active', 'Withdrawn'])->comment('ステータス');
            $table->string('banner_url', 255)->nullable();
            $table->string('category', 255);
            $table->string('title', 255);
            $table->text('html');
            $table->bigInteger('author_adm_user_id')->comment('作成者ユーザーID');
            $table->bigInteger('approval_adm_user_id')->nullable()->comment('承認者ユーザーID');
            $table->timestampTz('pre_notice_start_at')->comment('予告掲載開始日時');
            $table->timestampTz('start_at')->comment('開催期間開始日時');
            $table->timestampTz('end_at')->comment('開催期間終了日時');
            $table->timestampTz('post_notice_end_at')->comment('開催期間終了後の掲載終了日時');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_informations');
    }
};
