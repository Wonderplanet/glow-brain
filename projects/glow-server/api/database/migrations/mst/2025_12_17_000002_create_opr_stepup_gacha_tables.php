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
     */
    public function up(): void
    {
        Schema::create('opr_stepup_gachas', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->unsignedTinyInteger('max_step_number')->comment('最大ステップ数');
            $table->unsignedInteger('max_loop_count')->nullable()->comment('最大周回数');
            $table->timestampsTz();
            $table->comment('ステップアップガシャ設定');

            $table->unique(['opr_gacha_id'], 'opr_gacha_id_unique');
        });

        Schema::create('opr_stepup_gacha_steps', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->unsignedTinyInteger('step_number')->comment('ステップ番号');
            $table->enum('cost_type', ['Diamond','PaidDiamond','Free','Item'])->comment('コスト種別');
            $table->string('cost_id', 255)->nullable()->comment('コストID');
            $table->unsignedInteger('cost_num')->default(0)->comment('コスト数');
            $table->unsignedTinyInteger('draw_count')->comment('排出回数（何連ガシャか）');
            $table->unsignedTinyInteger('fixed_prize_count')->default(0)->comment('確定枠数（0-10）');
            $table->enum('fixed_prize_rarity_threshold_type', ['N','R','SR','SSR','UR'])->nullable()->comment('確定枠レアリティ条件');
            $table->string('prize_group_id', 255)->nullable()->comment('賞品グループID');
            $table->string('fixed_prize_group_id', 255)->nullable()->comment('確定枠賞品グループID');
            $table->boolean('is_first_free')->default(false)->comment('初回のみ無料フラグ');
            $table->timestampsTz();

            $table->unique(['opr_gacha_id', 'step_number'], 'opr_gacha_id_step_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_stepup_gachas');
        Schema::dropIfExists('opr_stepup_gacha_steps');
    }
};
