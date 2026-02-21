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
        Schema::table('usr_gachas', function (Blueprint $table) {
            $table->unsignedTinyInteger('current_step_number')->nullable()->comment('現在のステップ番号')->after('expires_at');
            $table->unsignedInteger('loop_count')->nullable()->comment('現在の周回数')->after('current_step_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_gachas', function (Blueprint $table) {
            $table->dropColumn(['current_step_number', 'loop_count']);
        });
    }
};
