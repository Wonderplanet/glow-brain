<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\AdventBattle\Enums\AdventBattleClearRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\LogAdventBattleReward;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\UseCases\AdventBattleEndUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleTopUseCase;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Encyclopedia\Models\LogEncyclopediaReward;
use App\Domain\Encyclopedia\UseCases\EncyclopediaReceiveFirstCollectionRewardUseCase;
use App\Domain\Encyclopedia\UseCases\EncyclopediaReceiveRewardUseCase;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAdventBattleClearReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Unit\Models\UsrUnitSummary;
use App\Domain\User\Models\UsrUserParameter;
use Illuminate\Support\Facades\Redis;
use Tests\Support\Entities\CurrentUser;
use Tests\Support\Traits\TestLogTrait;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class LogEncyclopediaRewardTest extends BaseControllerTestCase
{
    use TestLogTrait;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_EncyclopediaReceiveRewardUseCase_exec_獲得した報酬ログが保存できている()
    {
        // 必要なマスタデータ作成
        $totalGradeLevel = 10;
        $mstUnitEncyclopediaRewardId = 'reward1';

        MstUnitEncyclopediaReward::factory()->create([
            'id' => $mstUnitEncyclopediaRewardId,
            'unit_encyclopedia_rank' => $totalGradeLevel,
            'resource_type' => RewardType::COIN->value,
            'resource_amount' => 100,
        ]);

        // ユーザデータ作成
        $usrUserId = $this->createUsrUser()->getId();
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $usrUserId]);
        $this->createDiamond($usrUserId, 1000);

        UsrUnitSummary::factory()->create([
            'usr_user_id' => $usrUserId,
            'grade_level_total_count' => $totalGradeLevel,
        ]);

        $useCase = $this->app->make(EncyclopediaReceiveRewardUseCase::class);
        $useCase->exec(
            new CurrentUser($usrUserId),
            collect([$mstUnitEncyclopediaRewardId]),
            1
        );

        // log_advent_battle_rewardsテーブルに1件記録されていること
        $this->assertDatabaseCount('log_encyclopedia_rewards', 1);
        $log = LogEncyclopediaReward::query()->first();
        $receivedRewards = json_decode($log->received_reward, true);
        $this->assertCount(1, $receivedRewards);
        $reward = $receivedRewards[0];
        $this->assertEquals(RewardType::COIN->value, $reward['resourceType']);
        $this->assertEquals(100, $reward['resourceAmount']);
    }
}
