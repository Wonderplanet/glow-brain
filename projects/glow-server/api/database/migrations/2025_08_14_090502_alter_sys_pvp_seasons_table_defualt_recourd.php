<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // リリース前週のデータ調整する
        DB::table('sys_pvp_seasons')->upsert(
            [
                'id' => '2025038',
                'start_at' => '2025-09-15 3:00:00',
                'end_at' => '2025-09-21 14:59:59',
                'closed_at' => '2025-09-22 1:59:59',
            ],
            ['id'],
            ['closed_at']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_pvp_seasons', function (Blueprint $table) {
            //
        });
    }
};
