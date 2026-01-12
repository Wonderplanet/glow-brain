<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Mission;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent;
use App\Domain\Mission\UseCases\MissionEventUpdateAndFetchUseCase;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstMissionEvent;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;

class MissionEventUpdateAndFetchUseCaseTest extends TestCase
{
    private MissionEventUpdateAndFetchUseCase $missionEventUpdateAndFetchUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->missionEventUpdateAndFetchUseCase = app(MissionEventUpdateAndFetchUseCase::class);
    }

    public function test_exec_イベントミッションが正しく含まれていること()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $user = new CurrentUser($usrUser->getId());
        $now = $this->fixTime();

        MstEvent::factory()->createMany([
            ['id' => 'mst_event_id_1', 'start_at' => $now->subHours(1)->toDateTimeString(), 'end_at' => $now->addHours(1)->toDateTimeString()],// 期間内_1
            ['id' => 'mst_event_id_2', 'start_at' => $now->subHours(1)->toDateTimeString(), 'end_at' => $now->addHours(1)->toDateTimeString()],// 期間内_2(※並行イベント開催の想定の為)
            ['id' => 'mst_event_id_3', 'start_at' => $now->subHours(2)->toDateTimeString(), 'end_at' => $now->subHours(1)->toDateTimeString()],// 期間外(過去)
            ['id' => 'mst_event_id_4', 'start_at' => $now->addHours(1)->toDateTimeString(),'end_at' => $now->addHours(2)->toDateTimeString()],// 期間外(未来)
        ]);

        $createMstEventIds = [
            'mst_event_id_1',
            'mst_event_id_2',
            'mst_event_id_3',
            'mst_event_id_4',
            'mst_event_id_5',// 期間外(イベントマスターが存在しないパターン)
        ];

        $mstMissions = [];// イベント・イベントデイリー共通
        $usrEventMissions = [];
        foreach ($createMstEventIds as $createMstEventId) {
            // 未クリア(ユーザーミッション無し)
            $mstMissions[] = [
                'id' => $createMstEventId.'_1',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT,
                'criterion_value' => 'stage_1',
                'criterion_count' => 1,
                'mst_event_id' => $createMstEventId
            ];
            // 未クリア(ユーザーミッションあり)
            $mstMissions[] = [
                'id' => $createMstEventId.'_2',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT,
                'criterion_value' => 'stage_1',
                'criterion_count' => 1,
                'mst_event_id' => $createMstEventId
            ];
            $usrEventMissions[] = [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => $createMstEventId.'_2',
                'status' => MissionStatus::UNCLEAR,
                'cleared_at' => null,
                'received_reward_at' => null
            ];
            $usrEventMissions[] = [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => $createMstEventId.'_2',
                'status' => MissionStatus::UNCLEAR,
                'cleared_at' => null,
                'received_reward_at' => null,
                'latest_reset_at' => $now->toDateTimeString(),
            ];
            // クリア済み、未受け取り
            $mstMissions[] = [
                'id' => $createMstEventId.'_3',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT,
                'criterion_value' => 'stage_3',
                'criterion_count' => 1,
                'mst_event_id' => $createMstEventId
            ];
            $usrEventMissions[] = [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => $createMstEventId.'_3',
                'status' => MissionStatus::CLEAR,
                'cleared_at' => $now->toDateTimeString(),
                'received_reward_at' => null
            ];
            $usrEventMissions[] = [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => $createMstEventId.'_3',
                'status' => MissionStatus::CLEAR,
                'cleared_at' => $now->toDateTimeString(),
                'received_reward_at' => null,
                'latest_reset_at' => $now->toDateTimeString(),
            ];
            // クリア済み、受け取り済み
            $mstMissions[] = [
                'id' => $createMstEventId.'_4',
                'criterion_type' => MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT,
                'criterion_value' => 'stage_4',
                'criterion_count' => 1,
                'mst_event_id' => $createMstEventId
            ];
            $usrEventMissions[] = [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT->getIntValue(),
                'mst_mission_id' => $createMstEventId.'_4',
                'status' => MissionStatus::RECEIVED_REWARD,
                'cleared_at' => $now->toDateTimeString(),
                'received_reward_at' => $now->toDateTimeString()
            ];
            $usrEventMissions[] = [
                'usr_user_id' => $usrUser->getId(),
                'mission_type' => MissionType::EVENT_DAILY->getIntValue(),
                'mst_mission_id' => $createMstEventId.'_4',
                'status' => MissionStatus::CLEAR,
                'cleared_at' => $now->toDateTimeString(),
                'received_reward_at' => null,
                'latest_reset_at' => $now->toDateTimeString(),
            ];
        }
        MstMissionEvent::factory()->createMany($mstMissions);
        MstMissionEventDaily::factory()->createMany($mstMissions);
        UsrMissionEvent::factory()->createMany($usrEventMissions);

        // Exercise
        $result = $this->missionEventUpdateAndFetchUseCase->exec($user);

        // Verify
        $usrMissionEventStatusDataList = $result->usrMissionEventStatusDataList
            ->groupBy(fn($usrMissionStatusData) => $usrMissionStatusData->getGroupId());
        $this->assertEquals(3, $usrMissionEventStatusDataList->get('mst_event_id_1')?->count() ?? 0);
        $this->assertEquals(3, $usrMissionEventStatusDataList->get('mst_event_id_2')?->count() ?? 0);
        $this->assertNull($usrMissionEventStatusDataList->get('mst_event_id_3'));
        $this->assertNull($usrMissionEventStatusDataList->get('mst_event_id_4'));
        $this->assertNull($usrMissionEventStatusDataList->get('mst_event_id_5'));

        $usrMissionEventStatusDataList = $result->usrMissionEventStatusDataList
            ->keyBy(fn($usrMissionStatusData) => $usrMissionStatusData->getMstMissionId());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_1_2');
        $this->assertFalse($usrMissionEventStatusData->getIsCleared());
        $this->assertFalse($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_1_3');
        $this->assertTrue($usrMissionEventStatusData->getIsCleared());
        $this->assertFalse($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_1_4');
        $this->assertTrue($usrMissionEventStatusData->getIsCleared());
        $this->assertTrue($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_2_2');
        $this->assertFalse($usrMissionEventStatusData->getIsCleared());
        $this->assertFalse($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_2_3');
        $this->assertTrue($usrMissionEventStatusData->getIsCleared());
        $this->assertFalse($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_2_4');
        $this->assertTrue($usrMissionEventStatusData->getIsCleared());
        $this->assertTrue($usrMissionEventStatusData->getIsReceivedReward());

        $usrMissionEventDailyStatusDataList = $result->usrMissionEventDailyStatusDataList
            ->groupBy(fn($usrMissionStatusData) => $usrMissionStatusData->getGroupId());
        $this->assertEquals(3, $usrMissionEventDailyStatusDataList->get('mst_event_id_1')?->count() ?? 0);
        $this->assertEquals(3, $usrMissionEventDailyStatusDataList->get('mst_event_id_2')?->count() ?? 0);
        $this->assertEquals(0, $usrMissionEventDailyStatusDataList->get('mst_event_id_3')?->count() ?? 0);
        $this->assertEquals(0, $usrMissionEventDailyStatusDataList->get('mst_event_id_4')?->count() ?? 0);
        $this->assertEquals(0, $usrMissionEventDailyStatusDataList->get('mst_event_id_5')?->count() ?? 0);

        $usrMissionEventDailyStatusDataList = $result->usrMissionEventDailyStatusDataList
            ->groupBy(fn($usrMissionStatusData) => $usrMissionStatusData->getMstMissionId());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_1_2');
        $this->assertFalse($usrMissionEventStatusData->getIsCleared());
        $this->assertFalse($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_1_3');
        $this->assertTrue($usrMissionEventStatusData->getIsCleared());
        $this->assertFalse($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_1_4');
        $this->assertTrue($usrMissionEventStatusData->getIsCleared());
        $this->assertTrue($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_2_2');
        $this->assertFalse($usrMissionEventStatusData->getIsCleared());
        $this->assertFalse($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_2_3');
        $this->assertTrue($usrMissionEventStatusData->getIsCleared());
        $this->assertFalse($usrMissionEventStatusData->getIsReceivedReward());
        $usrMissionEventStatusData = $usrMissionEventStatusDataList->get('mst_event_id_2_4');
        $this->assertTrue($usrMissionEventStatusData->getIsCleared());
        $this->assertTrue($usrMissionEventStatusData->getIsReceivedReward());
    }
}
