<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Mission\Entities\MissionReceiveRewardStatus;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Services\MissionReceiveRewardService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Traits\TestMissionTrait;
use Tests\Support\Traits\TestRewardTrait;
use Tests\TestCase;

class MissionNormalBulkReceiveRewardServiceTest extends TestCase
{
    use TestMissionTrait;
    use TestRewardTrait;

    private MissionReceiveRewardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(MissionReceiveRewardService::class);
    }

    private function checkUsrMission(
        ?UsrMissionNormal $usrMission,
        int $status,
        int $progress,
        ?string $latestResetAt,
        ?string $clearedAt,
        ?string $receivedRewardAt,
    ) {
        if (is_null($usrMission)) {
            $this->fail('usrMission is null');
        }

        $this->assertEquals($status, $usrMission->getStatus(), 'status is not match');
        $this->assertEquals($progress, $usrMission->getProgress(), 'progress is not match');
        $this->assertEquals($latestResetAt, $usrMission->getLatestResetAt(), 'latest_reset_at is not match');
        $this->assertEquals($clearedAt, $usrMission->getClearedAt(), 'cleared_at is not match');
        $this->assertEquals($receivedRewardAt, $usrMission->getReceivedRewardAt(), 'received_reward_at is not match');
    }

    private function createUsrMission(
        string $usrUserId,
        MissionType $missionType,
        string $mstMissionId,
        MissionStatus $status,
        int $progress,
        ?string $clearedAt,
        ?string $receivedRewardAt,
        string $latestResetAt,
        MissionUnlockStatus $unlockStatus = MissionUnlockStatus::OPEN,
        int $unlockProgress = 0,
    ) {
        return UsrMissionNormal::factory()->create([
            'usr_user_id' => $usrUserId,
            'mission_type' => $missionType->getIntValue(),
            'mst_mission_id' => $mstMissionId,
            'status' => $status->value,
            'progress' => $progress,
            'latest_reset_at' => $latestResetAt,
            'cleared_at' => $clearedAt,
            'received_reward_at' => $receivedRewardAt,
            'is_open' => $unlockStatus?->value,
            'unlock_progress' => $unlockProgress,
        ]);
    }

    private function makeMasterData()
    {
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'item1'],
            ['id' => 'item2'],
            ['id' => 'item3'],
        ]);
        MstEmblem::factory()->createMany([
            ['id' => 'emblem1'],
        ]);
    }

    public static function params_test_bulkReceiveReward_時間経過リセットを考慮しつつ各ミッションタイプが別々に報酬付与と管理ができる(): array
    {
        return [
            'デイリー' => [MissionType::DAILY],
            'ウィークリー' => [MissionType::WEEKLY],
        ];
    }

    #[DataProvider('params_test_bulkReceiveReward_時間経過リセットを考慮しつつ各ミッションタイプが別々に報酬付与と管理ができる')]
    public function test_bulkReceiveReward_時間経過リセットを考慮しつつ各ミッションタイプが別々に報酬付与と管理ができる(MissionType $missionType)
    {
        // Setup
        $this->makeMasterData();

        $mstIdPrefix = $missionType->value;

        $usrUserId = $this->createUsrUser()->getId();

        $now = $this->fixTime('2024-10-10 05:00:00');
        $nowDateTimeString = $now->toDateTimeString();

        $clearedAt = '2024-10-09 05:00:00'; // クリア済となった日時

        // 現在日時(fixTime)と比較したときに書く日付設定
        // 要リセット判定になる日時
        $resettableLatestResetAts = [
            MissionType::DAILY->value => '2024-10-09 00:00:00',
            MissionType::WEEKLY->value => '2024-10-06 00:00:00',
        ];
        $resettableLatestResetAt = $resettableLatestResetAts[$missionType->value];

        // mst
        // ボーナスポイントミッション以外は、ボーナスポイント配布のみで報酬は無し
        $this->createMstMission($missionType, $mstIdPrefix.'1', MissionCriterionType::COIN_COLLECT, null, 10, '', 10);
        $this->createMstMission($missionType, $mstIdPrefix.'2', MissionCriterionType::COIN_COLLECT, null, 20, '', 20);
        $this->createMstMission($missionType, $mstIdPrefix.'3', MissionCriterionType::COIN_COLLECT, null, 30, '', 30);
        $this->createMstMission($missionType, $mstIdPrefix.'4', MissionCriterionType::COIN_COLLECT, null, 50, '', 100);
        $this->createMstMission($missionType, $mstIdPrefix.'10', MissionCriterionType::COIN_COLLECT, null, 999, '', 100);
        $this->createMstMission($missionType, $mstIdPrefix.'11', MissionCriterionType::COIN_COLLECT, null, 400, '', 100);
        // ボーナスポイントミッション
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint1', MissionCriterionType::MISSION_BONUS_POINT, null, 20, 'rewardGroup1', 0);
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint2', MissionCriterionType::MISSION_BONUS_POINT, null, 50, 'rewardGroup2', 0);
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint4', MissionCriterionType::MISSION_BONUS_POINT, null, 60, 'rewardGroup1', 0);
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint10', MissionCriterionType::MISSION_BONUS_POINT, null, 999, 'rewardGroup1', 0);
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint11', MissionCriterionType::MISSION_BONUS_POINT, null, 60, '', 0); // 受け取りステータスのみ確認したいだけなので報酬は無し
        // 報酬
        // rewardGroup1
        $this->createMstReward('rewardGroup1', RewardType::EXP, null, 100);
        $this->createMstReward('rewardGroup1', RewardType::COIN, null, 200);
        $this->createMstReward('rewardGroup1', RewardType::FREE_DIAMOND, null, 300);
        $this->createMstReward('rewardGroup1', RewardType::ITEM, 'item1', 400);
        $this->createMstReward('rewardGroup1', RewardType::ITEM, 'item2', 500);
        $this->createMstReward('rewardGroup1', RewardType::EMBLEM, 'emblem1', 1);
        // rewardGroup2
        $this->createMstReward('rewardGroup2', RewardType::COIN, null, 200);

        // usr
        // 受取可
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'1', MissionStatus::CLEAR, 50, $clearedAt, null, $nowDateTimeString);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'2', MissionStatus::CLEAR, 50, $clearedAt, null, $nowDateTimeString);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'3', MissionStatus::CLEAR, 50, $clearedAt, null, $nowDateTimeString);
        // 受け取り済みは受け取りされない
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'4', MissionStatus::RECEIVED_REWARD, 50, $clearedAt, $clearedAt, $nowDateTimeString);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'_bonusPoint4', MissionStatus::RECEIVED_REWARD, 60, $clearedAt, $clearedAt, $nowDateTimeString);
        // リセットされるので受取不可
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'10', MissionStatus::CLEAR, 999, $clearedAt, null, $resettableLatestResetAt);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'_bonusPoint10', MissionStatus::CLEAR, 999, $clearedAt, null, $resettableLatestResetAt);
        // リセットされるが進捗更新後に受取可になる
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'11', MissionStatus::CLEAR, 999, $clearedAt, null, $resettableLatestResetAt);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'_bonusPoint11', MissionStatus::CLEAR, 999, $clearedAt, null, $resettableLatestResetAt);

        // その他必要なもの設定
        $this->createDiamond($usrUserId, 0);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
        ]);

        // Exercise
        $receiveMstMissionIds = collect([
            $mstIdPrefix.'1',
            $mstIdPrefix.'2',
            $mstIdPrefix.'3',
            $mstIdPrefix.'4',
            $mstIdPrefix.'10',
            $mstIdPrefix.'11',
        ]);
        $this->service->bulkReceiveReward($usrUserId, $now, UserConstant::PLATFORM_IOS, $missionType, $receiveMstMissionIds);
        $this->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now, isSaveAll: false);
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // Verify
        // 報酬付与がされている
        $usrUserParameter->refresh();
        $this->assertEquals(100, $usrUserParameter->getExp());
        $this->assertEquals(3, $usrUserParameter->getLevel()); // exp=100でLv3になり、レベルアップ報酬が付与される
        $this->assertEquals(400, $usrUserParameter->getCoin());
        // diamond
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(300, $diamond->getFreeAmount());
        // item
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy(function (UsrItem $usrItem) {return $usrItem->getMstItemId();});
        $this->assertEquals(400, $usrItems->get('item1')->getAmount());
        $this->assertEquals(500, $usrItems->get('item2')->getAmount());
        // emblem
        $usrEmblem = UsrEmblem::where('usr_user_id', $usrUserId)->where('mst_emblem_id', 'emblem1')->first();
        $this->assertNotNull($usrEmblem);
        // ユーザーミッションのステータスが更新されている
        // 受取したミッション
        $usrMissions = UsrMissionNormal::query()->where('usr_user_id', $usrUserId)->get()->keyBy(function (UsrMissionNormal $usrMission) {return $usrMission->getMstMissionId();});
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'1'), MissionStatus::RECEIVED_REWARD->value, 50, $nowDateTimeString, $clearedAt, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'2'), MissionStatus::RECEIVED_REWARD->value, 50, $nowDateTimeString, $clearedAt, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'3'), MissionStatus::RECEIVED_REWARD->value, 50, $nowDateTimeString, $clearedAt, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint1'), MissionStatus::RECEIVED_REWARD->value, 20, $nowDateTimeString, $nowDateTimeString, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint2'), MissionStatus::RECEIVED_REWARD->value, 50, $nowDateTimeString, $nowDateTimeString, $nowDateTimeString);
        // リセットされるので受取されなかったミッション
        // 報酬コインが配布されるのでリセット後に進捗値は進む
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'10'), MissionStatus::UNCLEAR->value, 400, $nowDateTimeString, null, null);
        // ボーナスポイント付与されるのでリセット後に進捗値は進む
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint10'), MissionStatus::UNCLEAR->value, 60, $nowDateTimeString, null, null);
        // リセットされるが進捗更新後に受取可になるミッション
        // ボーナスポイント報酬によってトリガーされたミッションは受け取り対象にならないので、クリア状態で止まる
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'11'), MissionStatus::CLEAR->value, 400, $nowDateTimeString, $nowDateTimeString, null);
        // 必要なボーナスポイントちょうどに達しているのでクリアした直後に受取対象になる
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint11'), MissionStatus::RECEIVED_REWARD->value, 60, $nowDateTimeString, $nowDateTimeString, $nowDateTimeString);
        // 受取済はそのままでステータス変更がない
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'4'), MissionStatus::RECEIVED_REWARD->value, 50, $now, $clearedAt, $clearedAt);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint4'), MissionStatus::RECEIVED_REWARD->value, 60, $now, $clearedAt, $clearedAt);
    }

    public static function params_test_bulkReceiveReward_リセットなしボーナスポイントありの各ミッションタイプが別々に報酬付与と管理ができる(): array
    {
        return [
            '初心者' => [MissionType::BEGINNER],
        ];
    }

    #[DataProvider('params_test_bulkReceiveReward_リセットなしボーナスポイントありの各ミッションタイプが別々に報酬付与と管理ができる')]
    public function test_bulkReceiveReward_リセットなしボーナスポイントありの各ミッションタイプが別々に報酬付与と管理ができる(MissionType $missionType)
    {
        // Setup
        $this->makeMasterData();

        $mstIdPrefix = $missionType->value;

        $usrUserId = $this->createUsrUser()->getId();

        $now = $this->fixTime('2024-10-10 05:00:00');
        $nowDateTimeString = $now->toDateTimeString();

        $clearedAt = '2024-10-09 05:00:00'; // クリア済となった日時

        // mst
        // ボーナスポイントミッション以外は、ボーナスポイント配布のみで報酬は無し
        $this->createMstMission($missionType, $mstIdPrefix.'1', MissionCriterionType::COIN_COLLECT, null, 10, '', 10);
        $this->createMstMission($missionType, $mstIdPrefix.'2', MissionCriterionType::COIN_COLLECT, null, 20, '', 20);
        $this->createMstMission($missionType, $mstIdPrefix.'3', MissionCriterionType::COIN_COLLECT, null, 30, '', 30);
        $this->createMstMission($missionType, $mstIdPrefix.'4', MissionCriterionType::COIN_COLLECT, null, 50, '', 100);
        $this->createMstMission($missionType, $mstIdPrefix.'10', MissionCriterionType::COIN_COLLECT, null, 999, '', 100);
        $this->createMstMission($missionType, $mstIdPrefix.'11', MissionCriterionType::COIN_COLLECT, null, 400, '', 100);
        // ボーナスポイントミッション
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint1', MissionCriterionType::MISSION_BONUS_POINT, null, 20, 'rewardGroup1', 0);
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint2', MissionCriterionType::MISSION_BONUS_POINT, null, 50, 'rewardGroup2', 0);
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint4', MissionCriterionType::MISSION_BONUS_POINT, null, 60, 'rewardGroup1', 0);
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint10', MissionCriterionType::MISSION_BONUS_POINT, null, 999, 'rewardGroup1', 0);
        $this->createMstMission($missionType, $mstIdPrefix.'_bonusPoint11', MissionCriterionType::MISSION_BONUS_POINT, null, 60, '', 0); // 受け取りステータスのみ確認したいので報酬は無し
        // 進捗は進むが、未開放なのでクリアしても受取不可のミッション
        $this->createMstMission($missionType, $mstIdPrefix.'21', MissionCriterionType::COIN_COLLECT, null, 10, null, 999, null, 999, MissionCriterionType::DEFEAT_ENEMY_COUNT, 999);
        // 報酬
        // rewardGroup1
        $this->createMstReward('rewardGroup1', RewardType::EXP, null, 100);
        $this->createMstReward('rewardGroup1', RewardType::COIN, null, 200);
        $this->createMstReward('rewardGroup1', RewardType::FREE_DIAMOND, null, 300);
        $this->createMstReward('rewardGroup1', RewardType::ITEM, 'item1', 400);
        $this->createMstReward('rewardGroup1', RewardType::ITEM, 'item2', 500);
        $this->createMstReward('rewardGroup1', RewardType::EMBLEM, 'emblem1', 1);
        // rewardGroup2
        $this->createMstReward('rewardGroup2', RewardType::COIN, null, 200);

        // usr
        // 受取可
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'1', MissionStatus::CLEAR, 10, $clearedAt, null, $clearedAt);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'2', MissionStatus::CLEAR, 20, $clearedAt, null, $clearedAt);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'3', MissionStatus::CLEAR, 30, $clearedAt, null, $clearedAt);
        // 受け取り済みは受け取りされない
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'4', MissionStatus::RECEIVED_REWARD, 50, $clearedAt, $clearedAt, $clearedAt);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'_bonusPoint4', MissionStatus::RECEIVED_REWARD, 60, $clearedAt, $clearedAt, $clearedAt);
         // 進捗は進むが、未開放なのでクリアしても受取不可のミッション
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'21', MissionStatus::UNCLEAR, 1, $clearedAt, null, $clearedAt, MissionUnlockStatus::LOCK, 0,);

        // その他必要なもの設定
        $this->prepareUpdateBeginnerMission($usrUserId); // 初心者ミッションの進捗判定処理を実行するために用意
        $this->createDiamond($usrUserId, 0);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
        ]);

        // Exercise

        $this->service->bulkReceiveReward($usrUserId, $now, UserConstant::PLATFORM_IOS, $missionType, collect([
            $mstIdPrefix.'1',
            $mstIdPrefix.'2',
            $mstIdPrefix.'3',
            $mstIdPrefix.'4',
            $mstIdPrefix.'10',
            $mstIdPrefix.'11',

        ]));
        $this->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now, isSaveAll: false);
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // Verify
        // 報酬付与がされている
        $usrUserParameter->refresh();
        $this->assertEquals(100, $usrUserParameter->getExp());
        $this->assertEquals(3, $usrUserParameter->getLevel());
        $this->assertEquals(400, $usrUserParameter->getCoin());
        // diamond
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(300, $diamond->getFreeAmount());
        // item
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy(function (UsrItem $usrItem) {return $usrItem->getMstItemId();});
        $this->assertEquals(400, $usrItems->get('item1')->getAmount());
        $this->assertEquals(500, $usrItems->get('item2')->getAmount());
        // emblem
        $usrEmblem = UsrEmblem::where('usr_user_id', $usrUserId)->where('mst_emblem_id', 'emblem1')->first();
        $this->assertNotNull($usrEmblem);
        // ユーザーミッションのステータスが更新されている
        // 受取したミッション
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, $missionType);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'1'), MissionStatus::RECEIVED_REWARD->value, 10, $clearedAt, $clearedAt, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'2'), MissionStatus::RECEIVED_REWARD->value, 20, $clearedAt, $clearedAt, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'3'), MissionStatus::RECEIVED_REWARD->value, 30, $clearedAt, $clearedAt, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint1'), MissionStatus::RECEIVED_REWARD->value, 20, $nowDateTimeString, $nowDateTimeString, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint2'), MissionStatus::RECEIVED_REWARD->value, 50, $nowDateTimeString, $nowDateTimeString, $nowDateTimeString);
        // ボーナスポイント付与で進捗値が進むが、未クリア
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint10'), MissionStatus::UNCLEAR->value, 60, $nowDateTimeString, null, null);
        // 必要なボーナスポイントちょうどに達しているのでクリアした直後に受取対象になる
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint11'), MissionStatus::RECEIVED_REWARD->value, 60, $nowDateTimeString, $nowDateTimeString, $nowDateTimeString);
        // 受取済はそのままでステータス変更がない
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'4'), MissionStatus::RECEIVED_REWARD->value, 50, $clearedAt, $clearedAt, $clearedAt);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'_bonusPoint4'), MissionStatus::RECEIVED_REWARD->value, 60, $clearedAt, $clearedAt, $clearedAt);
        // 進捗は進むが、未開放なのでクリアしても未受取のまま
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'21'), MissionStatus::CLEAR->value, 10, $clearedAt, $now, null);
    }

    public static function params_test_bulkReceiveReward_リセットなしの各ミッションタイプが別々に報酬付与と管理ができる(): array
    {
        return [
            'アチーブメント' => [MissionType::ACHIEVEMENT],
        ];
    }

    #[DataProvider('params_test_bulkReceiveReward_リセットなしの各ミッションタイプが別々に報酬付与と管理ができる')]
    public function test_bulkReceiveReward_リセットなしの各ミッションタイプが別々に報酬付与と管理ができる(MissionType $missionType)
    {
        // Setup
        $this->makeMasterData();

        $mstIdPrefix = $missionType->value;

        $usrUserId = $this->createUsrUser()->getId();

        $now = $this->fixTime('2024-10-10 05:00:00');
        $nowDateTimeString = $now->toDateTimeString();

        $clearedAt = '2024-10-09 05:00:00'; // クリア済となった日時

        // mst
        $this->createMstMission($missionType, $mstIdPrefix.'1', MissionCriterionType::COIN_COLLECT, null, 10, 'rewardGroup1', 10);
        $this->createMstMission($missionType, $mstIdPrefix.'2', MissionCriterionType::COIN_COLLECT, null, 20, 'rewardGroup2', 20);
        $this->createMstMission($missionType, $mstIdPrefix.'3', MissionCriterionType::COIN_COLLECT, null, 30, 'rewardGroup2', 30);
        $this->createMstMission($missionType, $mstIdPrefix.'4', MissionCriterionType::COIN_COLLECT, null, 50, 'rewardGroup2', 100);
        $this->createMstMission($missionType, $mstIdPrefix.'10', MissionCriterionType::COIN_COLLECT, null, 999, '', 100);
        $this->createMstMission($missionType, $mstIdPrefix.'11', MissionCriterionType::COIN_COLLECT, null, 400, '', 100);
        // 進捗は進むが、未開放なのでクリアしても受取不可のミッション
        $this->createMstMission($missionType, $mstIdPrefix.'21', MissionCriterionType::COIN_COLLECT, null, 10, null, 999, null, 999, MissionCriterionType::DEFEAT_ENEMY_COUNT, 999);
        // 報酬
        // rewardGroup1
        $this->createMstReward('rewardGroup1', RewardType::EXP, null, 100);
        $this->createMstReward('rewardGroup1', RewardType::COIN, null, 200);
        $this->createMstReward('rewardGroup1', RewardType::FREE_DIAMOND, null, 300);
        $this->createMstReward('rewardGroup1', RewardType::ITEM, 'item1', 400);
        $this->createMstReward('rewardGroup1', RewardType::ITEM, 'item2', 500);
        $this->createMstReward('rewardGroup1', RewardType::EMBLEM, 'emblem1', 1);
        // rewardGroup2
        $this->createMstReward('rewardGroup2', RewardType::COIN, null, 200);

        // usr
        // 受取可
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'1', MissionStatus::CLEAR, 10, $clearedAt, null, $clearedAt);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'2', MissionStatus::CLEAR, 20, $clearedAt, null, $clearedAt);
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'3', MissionStatus::CLEAR, 30, $clearedAt, null, $clearedAt);
        // 受け取り済みは受け取りされない
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'4', MissionStatus::RECEIVED_REWARD, 50, $clearedAt, $clearedAt, $clearedAt);
         // 進捗は進むが、未開放なのでクリアしても受取不可のミッション
        $this->createUsrMission($usrUserId, $missionType, $mstIdPrefix.'21', MissionStatus::UNCLEAR, 1, null, null, $clearedAt, MissionUnlockStatus::LOCK, 0,);

        // その他必要なもの設定
        $this->createDiamond($usrUserId, 0);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
        ]);

        // Exercise
        $this->service->bulkReceiveReward($usrUserId, $now, UserConstant::PLATFORM_IOS, $missionType, collect([
            $mstIdPrefix.'1',
            $mstIdPrefix.'2',
            $mstIdPrefix.'3',
            $mstIdPrefix.'4',
            $mstIdPrefix.'10',
            $mstIdPrefix.'11',
            $mstIdPrefix.'21',

        ]));
        $this->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now, isSaveAll: false);
        $this->handleAllUpdateTriggeredMissions($usrUserId, $now);

        // Verify
        // 報酬付与がされている
        $usrUserParameter->refresh();
        $this->assertEquals(100, $usrUserParameter->getExp());
        $this->assertEquals(3, $usrUserParameter->getLevel());
        $this->assertEquals(600, $usrUserParameter->getCoin());
        // diamond
        $diamond = $this->getDiamond($usrUserId);
        $this->assertEquals(300, $diamond->getFreeAmount());
        // item
        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()->keyBy(function (UsrItem $usrItem) {return $usrItem->getMstItemId();});
        $this->assertEquals(400, $usrItems->get('item1')->getAmount());
        $this->assertEquals(500, $usrItems->get('item2')->getAmount());
        // emblem
        $usrEmblem = UsrEmblem::where('usr_user_id', $usrUserId)->where('mst_emblem_id', 'emblem1')->first();
        $this->assertNotNull($usrEmblem);
        // ユーザーミッションのステータスが更新されている
        // 受取したミッション
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, $missionType);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'1'), MissionStatus::RECEIVED_REWARD->value, 10, $clearedAt, $clearedAt, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'2'), MissionStatus::RECEIVED_REWARD->value, 20, $clearedAt, $clearedAt, $nowDateTimeString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'3'), MissionStatus::RECEIVED_REWARD->value, 30, $clearedAt, $clearedAt, $nowDateTimeString);
        // 受取済はそのままでステータス変更がない
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'4'), MissionStatus::RECEIVED_REWARD->value, 50, $clearedAt, $clearedAt, $clearedAt);
        // 進捗は進むが、未開放なのでクリアしても未受取のまま
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'21'), MissionStatus::CLEAR->value, 10, $clearedAt, $now, null);
    }

    public function test_bulkReceiveReward_受け取り対象のmstMissionIdsに不正なデータも含まれる場合は受け取れなかったとレスポンスできている()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2025-02-26 00:00:00');
        $nowString = $now->toDateTimeString();

        // mst
        $missionType = MissionType::DAILY;
        $this->createMstMission($missionType, 'daily1', MissionCriterionType::COIN_COLLECT, null, 10, null, 30); // ボーナスポイント30獲得
        $this->createMstMission($missionType, 'daily2', MissionCriterionType::COIN_COLLECT, null, 20);
        $this->createMstMission($missionType, 'daily3', MissionCriterionType::COIN_COLLECT, null, 1);
        $this->createMstMission($missionType, 'daily_bonusPoint1', MissionCriterionType::MISSION_BONUS_POINT, null, 20); // daily1報酬受取時に達成

        // usr
        // 受取可
        $this->createUsrMission($usrUserId, $missionType, 'daily1', MissionStatus::CLEAR, 10, $now, null, $nowString);
        // 進捗足りず受取不可
        $this->createUsrMission($usrUserId, $missionType, 'daily2', MissionStatus::UNCLEAR, 19, null, null, $nowString);
        // 受取済で受取不可
        $this->createUsrMission($usrUserId, $missionType, 'daily3', MissionStatus::RECEIVED_REWARD, 1, $now, $now, $nowString);

        // Exercise
        $result = $this->service->bulkReceiveReward($usrUserId, $now, UserConstant::PLATFORM_IOS, $missionType, collect([
            // 受取可
            'daily1',
            // 受取不可
            'daily2', // 進捗足りず未クリア
            'daily3',  // 受取済
            'invalidId1', // 不正なデータ
            'invalidId2', // 不正なデータ
            // ボーナスポイント報酬は自動受取対象なので、ここでは指定はしない
        ]));

        // Verify
        $this->assertCount(6, $result);
        $actual = $result->groupBy(function (MissionReceiveRewardStatus $status) {return $status->getUnreceivedReason()?->value;});
        $this->assertCount(2, $actual->get(null)); // daily1 と daily_bonusPoint1
        $this->assertCount(4, $actual->get(UnreceivedRewardReason::INVALID_DATA->value));
    }
}
