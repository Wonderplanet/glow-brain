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
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->dropColumn('rarity_group_id');
            $table->dropColumn('multi_rarity_group_id');
            $table->dropColumn('normal_prize_group_id');
            $table->dropColumn('limited_prize_group_id');
            $table->unsignedSmallInteger('multi_fixed_prize_count')->default(0)->nullable()->comment('N連の確定枠数')->after('multi_draw_count');
            $table->string('prize_group_id')->comment('opr_gacha_prizes.group_id')->after('total_ad_limit_count');
        });
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->string('fixed_prize_group_id')->nullable()->default(null)->comment('確定枠(opr_gacha_prizes.group_id)')->after('prize_group_id');
        });
        Schema::create('opr_gacha_prizes', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('group_id', 255)->comment('同じ抽選テーブルとしてまとめるグループID');
            $table->enum('resource_type', [
                'Coin',
                'Unit',
                'Item',
            ]);
            $table->string('resource_id', 255)->nullable();
            $table->unsignedInteger('resource_amount')->nullable();
            $table->unsignedBigInteger('weight')->default(1)->comment('出現比重');
            $table->boolean('pickup')->default(false)->comment('ピックアップ対象');
            $table->timestamps();
            $table->unique(['group_id', 'resource_type', 'resource_id'], 'group_id_resource_type_resource_id_unique');
        });
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->string('max_rarity_upper_description', 255)->nullable()->default('')->comment('最高レアリティ天井の文言')->after('description');
        });
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->string('pickup_upper_description', 255)->nullable()->default('')->comment('ピックアップ天井の文言')->after('max_rarity_upper_description');
        });
        Schema::dropIfExists('opr_gacha_limited_prizes');
        Schema::dropIfExists('opr_gacha_normal_prizes');
        Schema::dropIfExists('opr_gacha_rarities_weights');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_gacha_prizes');

        $resourceTypes = [
            'FreeDiamond',
            'Coin',
            'Exp',
            'Stamina',
            'Unit',
            'Item',
        ];
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->dropColumn('prize_group_id');
            $table->dropColumn('fixed_prize_group_id');
            $table->dropColumn('multi_fixed_prize_count');
            $table->string('rarity_group_id', 255)->comment('opr_gacha_rarities.id')->after('total_ad_limit_count');
        });
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->string('multi_rarity_group_id', 255)->nullable()->default(null)->comment('opr_gacha_rarities.id')->after('rarity_group_id');
        });
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->string('normal_prize_group_id', 255)->comment('opr_gacha_permanent_prizes.group_id')->after('multi_rarity_group_id');
        });
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->string('limited_prize_group_id', 255)->nullable()->default(null)->comment('opr_gacha_limited_prizes.group_id')->after('normal_prize_group_id');
        });
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->dropColumn('max_rarity_upper_description');
            $table->dropColumn('pickup_upper_description');
        });

        Schema::create('opr_gacha_rarities_weights', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->integer('normal_rarity')->unsigned();
            $table->integer('good_rarity')->unsigned();
            $table->integer('better_rarity')->unsigned();
            $table->integer('excellent_rarity')->unsigned();
            $table->integer('epic_rarity')->unsigned();
            $table->timestamps();
        });

        Schema::create('opr_gacha_limited_prizes', function (Blueprint $table) use ($resourceTypes) {
            $table->string('id', 255)->primary();
            $table->string('group_id', 255)->comment('同じ抽選テーブルとしてまとめるグループID');
            $table->enum('resource_type', $resourceTypes);
            $table->string('resource_id', 255)->nullable();
            $table->integer('resource_amount')->unsigned()->nullable();
            $table->text('resource_option')->nullable();
            $table->integer('weight')->unsigned()->default(1)->comment('出現比重');
            $table->boolean('pickup')->default(false)->comment('ピックアップ対象');
            $table->timestamps();
            $table->unique(['group_id', 'resource_type', 'resource_id'], 'group_id_resource_type_resource_id_unique');
        });

        Schema::create('opr_gacha_normal_prizes', function (Blueprint $table) use ($resourceTypes) {
            $table->string('id', 255)->primary();
            $table->string('group_id', 255)->comment('同じ抽選テーブルとしてまとめるグループID');
            $table->enum('resource_type', $resourceTypes);
            $table->string('resource_id', 255)->nullable();
            $table->integer('resource_amount')->unsigned()->nullable();
            $table->text('resource_option')->nullable();
            $table->integer('weight')->unsigned()->comment('出現比重');
            $table->boolean('pickup')->default(false)->comment('ピックアップ対象');
            $table->timestamps();
            $table->unique(['group_id', 'resource_type', 'resource_id'], 'group_id_resource_type_resource_id_unique');
        });
    }
};
