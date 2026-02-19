<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Domain\Constants\Database;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_shop_passes', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('opr_product_id', 255);
            $table->unsignedTinyInteger('is_display_expiration')->default(0)->comment('販売の有効期限を表示するかどうか 0:表示しない 1:表示する');
            $table->unsignedInteger('pass_duration_days')->comment('パスの有効日数');
            $table->string('asset_key');
            $table->bigInteger('release_key')->default(1);
            $table->unique('opr_product_id', 'uk_opr_product_id');
        });

        Schema::create('mst_shop_passes_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_shop_pass_id', 255);
            $table->enum('language', ['ja'])->default('ja');
            $table->string('name', 255);
            $table->bigInteger('release_key')->default(1);
            $table->unique(['mst_shop_pass_id', 'language'], 'uk_mst_shop_pass_id_language');
        });

        Schema::create('mst_shop_pass_rewards', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_shop_pass_id', 255);
            $table->enum('pass_reward_type', ['Daily', 'Immediately']);
            $table->enum('resource_type', ['Coin', 'FreeDiamond', 'Item']);
            $table->string('resource_id', 255)->nullable();
            $table->unsignedBigInteger('resource_amount');
            $table->bigInteger('release_key')->default(1);
            $table->index('mst_shop_pass_id', 'idx_mst_shop_pass_id');
        });

        $effectType = [
            'IdleIncentiveAddReward',
            'IdleIncentiveMaxQuickReceiveByDiamond',
            'IdleIncentiveMaxQuickReceiveByAd',
            'StaminaAddRecoveryLimit',
            'AdSkip',
            'ChangeBattleSpeed'
        ];
        Schema::create('mst_shop_pass_effects', function (Blueprint $table) use ($effectType) {
            $table->string('id', 255)->primary();
            $table->string('mst_shop_pass_id', 255);
            $table->enum('effect_type', $effectType);
            $table->unsignedBigInteger('effect_value')->nullable();
            $table->bigInteger('release_key')->default(1);
            $table->index('mst_shop_pass_id', 'idx_mst_shop_pass_id');
        });

        DB::statement("ALTER TABLE `opr_products` MODIFY COLUMN `product_type` ENUM('diamond','pack','pass');");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_shop_passes');
        Schema::dropIfExists('mst_shop_passes_i18n');
        Schema::dropIfExists('mst_shop_pass_rewards');
        Schema::dropIfExists('mst_shop_pass_effects');
        DB::statement("ALTER TABLE `opr_products` MODIFY COLUMN `product_type` ENUM('diamond','pack');");
    }
};
