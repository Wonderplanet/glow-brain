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
        if (config('app.env') === 'local' || config('app.env') === 'local_test') {
            return;
        }
        DB::statement("ALTER TABLE sys_pvp_seasons CACHE");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('app.env') === 'local' || config('app.env') === 'local_test') {
            return;
        }
        DB::statement("ALTER TABLE sys_pvp_seasons NOCACHE");
    }
};
