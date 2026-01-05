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
        Schema::table('adm_s3_objects', function (Blueprint $table) {
            $table->string('adm_promotion_tag_id', 255)
                ->nullable()
                ->comment('昇格タグID(adm_promotion_tags.id)')
                ->after('bucket_key_hash');

            $table->dropColumn('release_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_s3_objects', function (Blueprint $table) {
            $table->unsignedBigInteger('release_key')->nullable()->comment('リリースキー')->after('bucket_key_hash');

            $table->dropColumn('adm_promotion_tag_id');
        });
    }
};
