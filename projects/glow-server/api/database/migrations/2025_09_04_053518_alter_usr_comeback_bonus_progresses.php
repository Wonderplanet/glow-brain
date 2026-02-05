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
        Schema::table('usr_comeback_bonus_progresses', function (Blueprint $table) {
            $table->timestampTz('start_at')->comment('受取開始日時')->after('latest_update_at');
            $table->timestampTz('end_at')->comment('受取終了日時')->after('start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_comeback_bonus_progresses', function (Blueprint $table) {
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');
        });
    }
};
