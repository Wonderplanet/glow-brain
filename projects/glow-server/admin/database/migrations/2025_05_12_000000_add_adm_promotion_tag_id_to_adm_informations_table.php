<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adm_informations', function (Blueprint $table) {
            $table->string('adm_promotion_tag_id', 255)
                ->nullable()
                ->after('enable')
                ->comment('昇格タグID(adm_promotion_tags.id)');
            $table->index('adm_promotion_tag_id', 'idx_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_informations', function (Blueprint $table) {
            $table->dropIndex('idx_tag');
            $table->dropColumn('adm_promotion_tag_id');
        });
    }
};
