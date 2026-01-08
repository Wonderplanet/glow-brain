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
        // adm_s3_objects テーブル
        Schema::create('adm_s3_objects', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('bucket', 255)->comment('S3バケット名');
            $table->string('key', 2048)->comment('オブジェクトキー（パス）');
            $table->string('bucket_key_hash', 255)->comment('バケット名とオブジェクトパスのハッシュ')->unique('uk_bucket_key_hash');
            $table->unsignedBigInteger('release_key')->nullable()->comment('リリースキー');
            $table->unsignedBigInteger('size')->comment('サイズ(Byte)');
            $table->string('etag', 255)->comment('Etag');
            $table->string('content_type', 255)->comment('コンテンツタイプ');
            $table->string('upload_adm_user_id', 255)->comment('アップロードしたユーザー adm_users.id');
            $table->timestamp('last_modified_at')->comment('最終更新日時');
            $table->timestampsTz();
            $table->comment('S3オブジェクト管理テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_s3_objects');
    }
};
