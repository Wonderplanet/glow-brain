<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('log_bnid_links', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
        });
        Schema::table('log_logins', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
        });
        Schema::table('log_system_message_additions', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
            $table->index(['usr_user_id', 'trigger_source', 'trigger_value'], 'idx_user_id_and_trigger');
        });
        Schema::table('log_trade_shop_items', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
            $table->index(['usr_user_id', 'mst_shop_item_id'], 'idx_user_id_and_mst_shop_item_id');
        });
        Schema::table('log_artwork_fragments', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
            $table->index(['usr_user_id', 'content_type', 'target_id'], 'idx_user_id_and_contents');
            $table->index(['usr_user_id', 'mst_artwork_fragment_id'], 'idx_user_id_and_mst_artwork_fragment_id');
        });
        Schema::table('log_units', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
            $table->index(['usr_user_id', 'mst_unit_id'], 'idx_user_id_and_mst_unit_id');
        });
        Schema::table('log_tutorial_actions', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
        });
        Schema::table('log_ad_free_plays', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
            $table->index(['usr_user_id', 'content_type', 'target_id'], 'idx_user_id_and_contents');
        });
        Schema::table('log_idle_incentive_rewards', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
        });
        Schema::table('log_user_levels', function (Blueprint $table) {
            $table->index(['usr_user_id', 'created_at'], 'idx_user_id_and_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('log_bnid_links', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
        });
        Schema::table('log_logins', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
        });
        Schema::table('log_system_message_additions', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
            $table->dropIndex('idx_user_id_and_trigger');
        });
        Schema::table('log_trade_shop_items', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
            $table->dropIndex('idx_user_id_and_mst_shop_item_id');
        });
        Schema::table('log_artwork_fragments', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
            $table->dropIndex('idx_user_id_and_contents');
            $table->dropIndex('idx_user_id_and_mst_artwork_fragment_id');
        });
        Schema::table('log_units', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
            $table->dropIndex('idx_user_id_and_mst_unit_id');
        });
        Schema::table('log_tutorial_actions', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
        });
        Schema::table('log_ad_free_plays', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
            $table->dropIndex('idx_user_id_and_contents');
        });
        Schema::table('log_idle_incentive_rewards', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
        });
        Schema::table('log_user_levels', function (Blueprint $table) {
            $table->dropIndex('idx_user_id_and_created_at');
        });
    }
};
