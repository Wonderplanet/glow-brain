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
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->json('opr_campaign_ids')->nullable()->comment('opr_campaigns.idの配列')->after('continue_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->dropColumn('opr_campaign_ids');
        });
    }
};
