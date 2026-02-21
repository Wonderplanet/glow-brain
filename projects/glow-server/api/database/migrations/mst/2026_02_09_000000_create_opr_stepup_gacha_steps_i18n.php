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
        Schema::create('opr_stepup_gacha_steps_i18n', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('opr_stepup_gacha_step_id', 255)->comment('opr_stepup_gacha_steps.id');
            $table->enum('language', ['ja'])->comment('言語情報');
            $table->string('fixed_prize_description', 255)->default('')->comment('確定枠の表示文言（排出物テキスト）');
            $table->comment('ステップアップガシャステップ多言語設定');

            $table->unique(['opr_stepup_gacha_step_id', 'language'], 'opr_stepup_gacha_step_id_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_stepup_gacha_steps_i18n');
    }
};
