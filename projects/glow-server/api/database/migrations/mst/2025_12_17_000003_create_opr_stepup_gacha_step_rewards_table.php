<?php

declare(strict_types=1);

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
        Schema::create('opr_stepup_gacha_step_rewards', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->unsignedTinyInteger('step_number')->comment('ステップ番号');
            $table->unsignedInteger('loop_count_target')->nullable()->comment('対象周回数（NULL=全周回）');
            $table->enum('resource_type', ['Exp', 'Coin', 'FreeDiamond', 'Item', 'Emblem', 'Stamina', 'Unit'])->comment('報酬リソースタイプ');
            $table->string('resource_id', 255)->nullable()->default(null)->comment('報酬リソースID');
            $table->unsignedBigInteger('resource_amount')->comment('報酬数量');
            $table->timestampsTz();
            $table->comment('ステップアップガシャのステップごとおまけ報酬');

            $table->index(['opr_gacha_id', 'step_number'], 'idx_opr_gacha_id_step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_stepup_gacha_step_rewards');
    }
};
