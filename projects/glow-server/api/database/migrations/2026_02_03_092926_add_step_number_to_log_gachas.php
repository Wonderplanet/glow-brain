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
        Schema::table('log_gachas', function (Blueprint $table) {
            $table->unsignedTinyInteger('step_number')
                ->nullable()
                ->after('draw_count')
                ->comment('ステップ番号（ステップアップガシャ用、通常ガシャはnull）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_gachas', function (Blueprint $table) {
            $table->dropColumn('step_number');
        });
    }
};
