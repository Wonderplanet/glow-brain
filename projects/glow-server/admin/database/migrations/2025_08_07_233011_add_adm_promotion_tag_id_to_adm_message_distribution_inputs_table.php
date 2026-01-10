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
            $table->string('adm_promotion_tag_id')->nullable()->comment('昇格タグID')->after('account_created_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            $table->dropColumn('adm_promotion_tag_id');
        });
    }
};
