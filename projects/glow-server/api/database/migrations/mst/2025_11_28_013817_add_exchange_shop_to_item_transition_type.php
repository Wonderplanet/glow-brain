<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // glow-schema PR #497
    // ItemTransitionType に ExchangeShop を追加
    // https://github.com/Wonderplanet/glow-schema/pull/497

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
            'ExchangeShop',
            'Etc',
        ];

        // transition1カラムの更新
        DB::connection($this->connection)->statement(
            'ALTER TABLE mst_item_transitions MODIFY transition1 ENUM("' . implode('","', $itemTransitionTypes) . '") NOT NULL'
        );

        // transition2カラムの更新
        DB::connection($this->connection)->statement(
            'ALTER TABLE mst_item_transitions MODIFY transition2 ENUM("' . implode('","', $itemTransitionTypes) . '")'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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

        // transition1カラムを元に戻す
        DB::connection($this->connection)->statement(
            'ALTER TABLE mst_item_transitions MODIFY transition1 ENUM("' . implode('","', $itemTransitionTypes) . '") NOT NULL'
        );

        // transition2カラムを元に戻す
        DB::connection($this->connection)->statement(
            'ALTER TABLE mst_item_transitions MODIFY transition2 ENUM("' . implode('","', $itemTransitionTypes) . '")'
        );
    }
};
