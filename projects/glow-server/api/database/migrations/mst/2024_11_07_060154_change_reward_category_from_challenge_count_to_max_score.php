<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // すでにChallengeCountのデータが入っているので一旦MaxScoreを追加してデータを更新後ChallengeCountを削除する
        DB::statement("ALTER TABLE mst_advent_battle_reward_groups MODIFY COLUMN reward_category ENUM ('ChallengeCount','MaxScore','Ranking','Rank','RaidTotalScore') NOT NULL");

        DB::table('mst_advent_battle_reward_groups')
            ->where('reward_category', 'ChallengeCount')
            ->update(['reward_category' => 'MaxScore']);

        DB::statement("ALTER TABLE mst_advent_battle_reward_groups MODIFY COLUMN reward_category ENUM ('MaxScore','Ranking','Rank','RaidTotalScore') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // MaxScoreのデータが入っている可能性があるので一旦ChallengeCountを追加してデータを更新後MaxScoreを削除する
        DB::statement("ALTER TABLE mst_advent_battle_reward_groups MODIFY COLUMN reward_category ENUM ('ChallengeCount','MaxScore','Ranking','Rank','RaidTotalScore') NOT NULL");

        DB::table('mst_advent_battle_reward_groups')
            ->where('reward_category', 'MaxScore')
            ->update(['reward_category' => 'ChallengeCount']);

        DB::statement("ALTER TABLE mst_advent_battle_reward_groups MODIFY COLUMN reward_category ENUM ('ChallengeCount','Ranking','Rank','RaidTotalScore') NOT NULL");
    }
};
