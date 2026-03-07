<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // adm_s3_bucket_scopes テーブル
        Schema::create('adm_s3_bucket_scopes', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('bucket', 255)->comment('S3バケット名');
            $table->string('prefix', 255)->comment('プレフィックス（フォルダパス）');
            $table->string('memo', 255)->comment('管理用メモ');
            $table->timestampsTz();
            $table->comment('S3バケット・プレフィックス管理テーブル');

            // 同じバケット・プレフィックスの組み合わせが重複しないように一意制約を追加
            $table->unique(['bucket', 'prefix'], 'uk_bucket_prefix');
        });

        $bannerBucket = config('filesystems.disks.s3_banner.bucket');
        if ($bannerBucket !== null) {
            $now = now();
            DB::table('adm_s3_bucket_scopes')->insert([
                [
                    'id' => Str::orderedUuid()->toString(),
                    'bucket' => $bannerBucket,
                    'prefix' => 'gachabanner',
                    'memo' => 'ガチャバナー',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => Str::orderedUuid()->toString(),
                    'bucket' => $bannerBucket,
                    'prefix' => 'homebanner',
                    'memo' => 'ホームバナー',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_s3_bucket_scopes');
    }
};
