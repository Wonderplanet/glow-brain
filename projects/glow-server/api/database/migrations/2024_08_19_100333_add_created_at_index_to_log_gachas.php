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
        Schema::table('log_gachas', function (Blueprint $table) {
            $table->dropColumn('is_upper');
            $table->index(['created_at'], 'idx_created_at');
            $table->index(['created_at', 'opr_gacha_id'], 'idx_created_at_opr_gacha_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_gachas', function (Blueprint $table) {
            $table->dropIndex('idx_created_at_opr_gacha_id');
            $table->dropIndex('idx_created_at');
            $table->boolean('is_upper')->comment('0:天井に達していない 1:天井に達した');
        });
    }
};
