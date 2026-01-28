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
        $resourceTypes = [
            'FreeDiamond',
            'Coin',
            'Exp',
            'Stamina',
            'Unit',
            'Item',
        ];

        $gachaTypes = [
            'Normal',
            'Premium',
            'Pickup',
            'Stepup',
            'Free',
            'Ticket',
            'Festival'
        ];

        $costTypes = [
            'Diamond',
            'PaidDiamond',
            'Free',
            'Item',
            'Ad',
            'Coin'
        ];

        $languages = [
            'ja',
            'en',
            'zh-Hant',
        ];

        Schema::create('opr_gachas', function (Blueprint $table) use ($gachaTypes) {
            $table->string('id', 255)->primary();
            $table->enum('gacha_type', $gachaTypes)->default('Normal')->comment('ノーマルかプレミアムか');
            $table->string('upper_type')->default('None')->comment('天井設定区分');
            $table->boolean('enable_ad_play')->default(false)->comment('広告で回せるか');
            $table->boolean('enable_add_ad_play_upper')->default(false)->comment('広告で天井を動かすか');
            $table->integer('ad_play_interval_time')->unsigned()->nullable()->default(null)->comment('広告で回すことができるインターバル時間(設定単位は分)');
            $table->integer('multi_draw_count')->unsigned()->default(1)->comment('N連の指定');
            $table->integer('daily_play_limit_count')->unsigned()->nullable()->default(null)->comment('１日に回すことができる上限数');
            $table->integer('total_limit_play_limit')->unsigned()->nullable()->default(null)->comment('回すことができる上限数');
            $table->string('rarity_group_id', 255)->comment('opr_gacha_rarities.id');
            $table->string('multi_rarity_group_id', 255)->nullable()->default(null)->comment('opr_gacha_rarities.id');
            $table->string('permanent_prize_group_id', 255)->comment('opr_gacha_permanent_prizes.group_id');
            $table->string('limited_prize_group_id', 255)->nullable()->default(null)->comment('opr_gacha_limited_prizes.group_id');
            $table->timestampTz('start_at')->nullable()->default(null)->comment('開催期間');
            $table->timestampTz('end_at')->nullable()->default(null)->comment('開催期間');
            $table->text('pickup_mst_unit_id')->nullable()->default(null)->comment('表示に使用するピックアップユニットIDを指定する');
            $table->integer('gacha_priority')->default(1)->comment('バナー表示順');
            $table->enum('gacha_banner_size', ['SizeM', 'SizeL'])->default('SizeM')->comment('ガチャバナーサイズ');
            $table->timestamps();
        });

        Schema::create('opr_gacha_use_resources', function (Blueprint $table) use ($costTypes) {
            $table->string('id', 255)->primary();
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->enum('cost_type', $costTypes)->default('Diamond');
            $table->string('cost_id', 255)->nullable()->default(null)->comment('消費リソースID');
            $table->integer('cost_num')->unsigned()->default(1)->comment('一回で必要なアイテムの個数');
            $table->integer('draw_count')->unsigned()->default(1)->comment('リソースを1回分消費して回せる回数');
            $table->integer('cost_priority')->unsigned()->default(1)->comment('使用するコストの優先度設定');
            $table->timestamps();
            $table->unique(['opr_gacha_id', 'cost_type'], 'opr_gacha_id_cost_type_unique');
        });

        Schema::create('opr_gachas_i18n', function (Blueprint $table) use ($languages) {
            $table->string('id', 255)->primary();
            $table->string('opr_gacha_id', 255)->comment('opr_gachas.id');
            $table->enum('language', $languages)->default('ja')->comment('言語情報');
            $table->text('name')->nullable()->default(null)->comment('ガチャ名');
            $table->text('description')->nullable()->default(null)->comment('ガチャ説明');
            $table->text('banner_url')->nullable()->default(null)->comment('バナーURL');
            $table->text('logo_banner_url')->nullable()->default(null)->comment('詳細へ飛んだ後のロゴバナーurl');
            $table->text('background_url')->nullable()->default(null)->comment('背景URL');
            $table->timestamps();
            $table->unique(['opr_gacha_id'], 'opr_gacha_id_unique');
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

        Schema::create('opr_gacha_permanent_prizes', function (Blueprint $table) use ($resourceTypes) {
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

        Schema::create('opr_gacha_uppers', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('upper_type')->default('None')->comment('天井設定区分');
            $table->integer('step_number')->unsigned()->comment('天井回数');
            $table->integer('count')->unsigned()->comment('天井を保証する回数');
            $table->timestamps();
            $table->unique(['upper_type', 'step_number'], 'upper_type_step_number_unique');
        });

        // 古いテーブルの削除
        Schema::dropIfExists('opr_gacha_super_prizes');
        Schema::dropIfExists('opr_gacha_supers');
        Schema::dropIfExists('opr_gacha_supers_i18n');
        Schema::dropIfExists('opr_gacha_normal_prizes');
        Schema::dropIfExists('opr_gacha_normals');
        Schema::dropIfExists('opr_gacha_normals_i18n');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_gachas');
        Schema::dropIfExists('opr_gacha_use_resources');
        Schema::dropIfExists('opr_gachas_i18n');
        Schema::dropIfExists('opr_gacha_rarities_weights');
        Schema::dropIfExists('opr_gacha_limited_prizes');
        Schema::dropIfExists('opr_gacha_permanent_prizes');
        Schema::dropIfExists('opr_gacha_uppers');

        Schema::create('opr_gacha_super_prizes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('release_key')->default(1);
            $table->string('asset_key');
            $table->string('group_id');
            $table->string('mst_unit_id');
            $table->unsignedInteger('weight');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unique(['group_id', 'mst_unit_id']);
        });

        Schema::create('opr_gacha_supers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('release_key')->default(1);
            $table->string('asset_key');
            $table->unsignedInteger('multi_draw_count')->default(1);
            $table->unsignedInteger('single_required_diamond_amount')->default(1);
            $table->unsignedInteger('multi_required_diamond_amount')->default(1);
            $table->unsignedInteger('wish_count')->default(1);
            $table->unsignedFloat('wish_rate', 5, 2);
            $table->string('prize_group_id');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
        });

        Schema::create('opr_gacha_supers_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('release_key')->default(1);
            $table->string('asset_key');
            $table->string('opr_gacha_super_id');
            $table->string('language');
            $table->string('name');
            $table->string('description');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unique(['opr_gacha_super_id', 'language']);
        });
    }
};
