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
        Schema::table('usr_user_profiles', function (Blueprint $table) {
            $table->dropColumn('mst_avatar_id');
            $table->dropColumn('mst_avatar_frame_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_user_profiles', function (Blueprint $table) {
            $table->string('mst_avatar_id', 255)->default('');
            $table->string('mst_avatar_frame_id', 255)->default('');
        });
    }
};