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
        Schema::table('adm_informations', function (Blueprint $table) {
            $table->string('os_type')->after('status')->default('All')->comment('OSタイプ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_informations', function (Blueprint $table) {
            $table->dropColumn('os_type');
        });
    }
};
