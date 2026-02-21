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
        Schema::table('usr_units', function (Blueprint $table) {
            $table->tinyInteger('is_new_encyclopedia')->default(1)->comment('新規獲得フラグ')->after('battle_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_units', function (Blueprint $table) {
            $table->dropColumn('is_new_encyclopedia');
        });
    }
};
