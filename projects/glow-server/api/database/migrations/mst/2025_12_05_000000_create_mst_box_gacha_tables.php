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
        $loopTypes = [
            'All',
            'Last',
            'First',
        ];

        // BOXガチャで設定可能な報酬タイプ（BoxGachaRewardType）
        $resourceTypes = [
            'Item',
            'Artwork',
            'FreeDiamond',
            'Coin',
            'Unit',
        ];

        // BOXガチャマスターテーブル
        Schema::create('mst_box_gachas', function (Blueprint $table) use ($loopTypes) {
            $table->string('id', 255)->primary();
            $table->string('mst_event_id', 255)->comment('mst_events.id イベントID');
            $table->string('cost_id', 255)->comment('消費アイテムID（mst_items.id）');
            $table->integer('cost_num')->unsigned()->default(1)->comment('1回の抽選に必要なコスト数');
            $table->enum('loop_type', $loopTypes)->default('Last')->comment('ループタイプ');
            $table->timestamps();
        });

        // BOXガチャグループマスターテーブル（箱の定義）
        Schema::create('mst_box_gacha_groups', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_box_gacha_id', 255)->comment('mst_box_gachas.id');
            $table->integer('box_level')->unsigned()->comment('箱レベル（1から順番）');
            $table->timestamps();
            $table->unique(['mst_box_gacha_id', 'box_level'], 'mst_box_gacha_id_box_level_unique');
        });

        // BOXガチャ賞品マスターテーブル
        Schema::create('mst_box_gacha_prizes', function (Blueprint $table) use ($resourceTypes) {
            $table->string('id', 255)->primary();
            $table->string('mst_box_gacha_group_id', 255)->comment('mst_box_gacha_groups.id');
            $table->boolean('is_pickup')->default(false)->comment('ピックアップ対象');
            $table->enum('resource_type', $resourceTypes)->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable()->default(null)->comment('報酬リソースID');
            $table->integer('resource_amount')->unsigned()->default(1)->comment('報酬数量');
            $table->integer('stock')->unsigned()->default(1)->comment('在庫数');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_box_gacha_prizes');
        Schema::dropIfExists('mst_box_gacha_groups');
        Schema::dropIfExists('mst_box_gachas');
    }
};
