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
        Schema::create('mst_artwork_effect_target_rules', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->string('mst_artwork_effect_id')->comment('mst_artwork_effects.id');
            $table->string('condition_type')->comment('条件タイプ');
            $table->string('condition_value')->comment('条件値');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');

            $table->index('mst_artwork_effect_id');
            $table->comment('原画効果対象ルールマスタ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_artwork_effect_target_rules');
    }
};
