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
        $targetIdInputTypes = [
            'All',
            'Input',
            'Csv',
        ];
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) use ($targetIdInputTypes) {
            $table->enum('display_target_id_input_type', $targetIdInputTypes)->default('All')->after('target_ids_txt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            $table->dropColumn('display_target_id_input_type');
        });
    }
};
