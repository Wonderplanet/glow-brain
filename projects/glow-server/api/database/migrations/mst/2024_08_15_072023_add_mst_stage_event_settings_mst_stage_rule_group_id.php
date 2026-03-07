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
        Schema::table('mst_stage_event_settings', function (Blueprint $table) {
            $table->string('mst_stage_rule_group_id', 255)->nullable()->default(null)->comment('mst_stage_event_rules.group_id')->after('ad_challenge_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_stage_event_settings', function (Blueprint $table) {
            $table->dropColumn('mst_stage_rule_group_id');
        });
    }
};
