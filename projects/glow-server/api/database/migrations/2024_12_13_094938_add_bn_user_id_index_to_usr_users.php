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
        Schema::table('usr_users', function (Blueprint $table) {
            $table->index('bn_user_id', 'bn_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->dropIndex('bn_user_id_index');
        });
    }
};
