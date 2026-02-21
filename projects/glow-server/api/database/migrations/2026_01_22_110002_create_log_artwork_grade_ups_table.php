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
        Schema::create('log_artwork_grade_ups', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('nginx_request_id')->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id')->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->string('mst_artwork_id')->comment('グレードアップした原画(mst_artworks.id)');
            $table->integer('before_grade_level')->comment('強化前のグレードレベル');
            $table->integer('after_grade_level')->comment('強化後のグレードレベル');
            $table->timestampsTz();

            $table->index('usr_user_id', 'idx_usr_user_id');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_artwork_grade_ups');
    }
};
