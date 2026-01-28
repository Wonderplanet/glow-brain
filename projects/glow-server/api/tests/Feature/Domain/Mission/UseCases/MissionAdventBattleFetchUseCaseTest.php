<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\UseCases\MissionAdventBattleFetchUseCase;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class MissionAdventBattleFetchUseCaseTest extends TestCase
{
    private MissionAdventBattleFetchUseCase $missionAdventBattleFetchUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->missionAdventBattleFetchUseCase = app(MissionAdventBattleFetchUseCase::class);
    }

    public function test_exec_降臨バトル関連のミッションが含まれていること()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->createUsrUser();
        $user = new CurrentUser($usrUser->getId());

        /**
         * 期間限定ミッション
         */
        MstMissionLimitedTerm::factory()->createMany([
            // 期間限定ミッション-期間内
            [
                'id' => 'mst_mission_limited_term_1',
                'progress_group_key' => 'progress_group_key_1',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE,
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString(),
            ],
            // 期間限定ミッション-期間外
            [
                'id' => 'mst_mission_limited_term_2',
                'progress_group_key' => 'progress_group_key_2',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE,
                'start_at' => $now->subHours(2)->toDateTimeString(),
                'end_at' => $now->subHours(1)->toDateTimeString(),
            ],
        ]);
        UsrMissionLimitedTerm::factory()->createMany([
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_mission_limited_term_id' => 'mst_mission_limited_term_1',
                'status' => MissionStatus::UNCLEAR,
                'cleared_at' => null,
                'received_reward_at' => null
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_mission_limited_term_id' => 'mst_mission_limited_term_2',
                'status' => MissionStatus::UNCLEAR,
                'cleared_at' => null,
                'received_reward_at' => null
            ],
        ]);

        // Exercise
        $result = $this->missionAdventBattleFetchUseCase->exec($user);

        // Verify
        $actuals = $result->usrMissionLimitedTermStatusDataList
            ->keyBy(fn($usrMissionStatusData) => $usrMissionStatusData->getMstMissionId());
        $this->assertNotNull($actuals->get('mst_mission_limited_term_1'));
        $this->assertNull($actuals->get('mst_mission_limited_term_2'));
    }
}
