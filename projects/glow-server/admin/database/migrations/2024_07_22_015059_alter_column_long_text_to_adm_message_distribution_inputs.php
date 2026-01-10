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
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            // text型からlong text型に変更
            $table->longText('target_ids_txt')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            $table->text('target_ids_txt')->nullable()->change();
        });
    }
};
