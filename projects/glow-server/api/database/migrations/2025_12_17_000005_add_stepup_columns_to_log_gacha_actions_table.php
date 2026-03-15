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
        Schema::table('log_gacha_actions', function (Blueprint $table) {
            $table->unsignedTinyInteger('step_number')->nullable()->comment('実行したステップ番号')->after('pickup_upper_count');;
            $table->unsignedInteger('loop_count')->nullable()->comment('実行時の周回数')->after('step_number');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_gacha_actions', function (Blueprint $table) {
            $table->dropColumn(['step_number', 'loop_count']);
        });
    }
};
