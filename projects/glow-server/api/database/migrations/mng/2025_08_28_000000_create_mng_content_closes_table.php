<?php
use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MNG_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mng_content_closes', function (Blueprint $table) {
            $table->string('id')->primary()->comment('管理コンテンツクローズID');
            $table->string('content_type')->nullable(false)->comment('コンテンツタイプ');
            $table->string('content_id')->nullable()->comment('コンテンツID（ガチャID、ショップIDなど。null = 全コンテンツ）');
            $table->timestampTz('start_at')->comment('クローズ開始時間');
            $table->timestampTz('end_at')->comment('クローズ終了時間');
            $table->tinyInteger('is_valid')->default(1)->comment('有効フラグ');
            
            // content_type + content_id の組み合わせでインデックスを作成
            $table->index(['content_type', 'content_id'], 'idx_content_type_content_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mng_content_closes');
    }
};
