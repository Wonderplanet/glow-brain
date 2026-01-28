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
        Schema::table('usr_devices', function (Blueprint $table) {
            $table->index('usr_user_id', 'idx_usr_user_id');
        });

        Schema::table('log_banks', function (Blueprint $table) {
            $table->index(['created_at', 'id'], 'idx_created_at_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_devices', function (Blueprint $table) {
            $table->dropIndex('idx_usr_user_id');
        });

        Schema::table('log_banks', function (Blueprint $table) {
            $table->dropIndex('idx_created_at_id');
        });
    }
};
