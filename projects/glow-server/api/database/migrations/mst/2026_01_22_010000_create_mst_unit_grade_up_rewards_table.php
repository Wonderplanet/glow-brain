<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     *
     * キャラグレード5到達原画機能
     * キャラIDとグレードレベルに対して獲得可能な報酬のマッピング情報を管理
     */
    public function up(): void
    {
        Schema::create('mst_unit_grade_up_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_unit_id', 255)->comment('mst_units.id');
            $table->unsignedInteger('grade_level')->comment('報酬獲得可能グレードレベル');
            $table->enum('resource_type', ['Artwork'])->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable()->comment('報酬ID');
            $table->unsignedInteger('resource_amount')->comment('報酬数量');
            $table->timestampsTz();
            $table->comment('キャラグレードアップ報酬');

            $table->index(['mst_unit_id'], 'idx_mst_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_unit_grade_up_rewards');
    }
};
