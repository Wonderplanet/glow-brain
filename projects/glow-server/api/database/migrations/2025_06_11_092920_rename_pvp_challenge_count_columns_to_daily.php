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
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->renameColumn('remaining_challenge_count', 'daily_remaining_challenge_count');
            $table->renameColumn('remaining_item_challenge_count', 'daily_remaining_item_challenge_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->renameColumn('daily_remaining_challenge_count', 'remaining_challenge_count');
            $table->renameColumn('daily_remaining_item_challenge_count', 'remaining_item_challenge_count');
        });
    }
};
