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
        Schema::table('usr_artworks', function (Blueprint $table) {
            $table->integer('grade_level')->default(1)->comment('グレード')->after('is_new_encyclopedia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_artworks', function (Blueprint $table) {
            $table->dropColumn('grade_level');
        });
    }
};
