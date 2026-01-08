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
        //
        Schema::create('usr_unit_summaries', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('usr_user_id', 255)->primary()->comment('usr_users.id');
            $table->unsignedInteger('grade_level_total_count')->default(0)->comment('UserごとのUnitGradeUp回数');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('usr_unit_summaries');
    }
};
