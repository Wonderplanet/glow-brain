<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 新規テーブル追加
    // - name: MstItemTransition
    //   obscure: true
    //   impl_entity: true
    //   params:
    //     - name: id
    //       type: string
    //     - name: mstItemId
    //       type: string
    //     - name: transition1
    //       type: ItemTransitionType
    //     - name: transition1MstId
    //       type: string
    //     - name: transition2
    //       type: ItemTransitionType
    //     - name: transition2MstId
    //       type: string

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $itemTransitionTypes = [
            'None',
            'MainQuest',
            'EventQuest',
            'ShopItem',
            'Pack',
            'Achievement',
            'LoginBonus',
            'DailyMission',
            'WeeklyMission',
            'Patrol',
            'Etc',
        ];
        Schema::create('mst_item_transitions', function (Blueprint $table) use ($itemTransitionTypes) {
            $table->string('id', 255)->primary();
            $table->string('mst_item_id')->nullable(false);
            $table->enum('transition1', $itemTransitionTypes)->nullable(false);
            $table->string('transition1_mst_id')->nullable(false);
            $table->enum('transition2', $itemTransitionTypes)->nullable();
            $table->string('transition2_mst_id')->nullable();
            $table->bigInteger('release_key')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_item_transitions');
    }
};
