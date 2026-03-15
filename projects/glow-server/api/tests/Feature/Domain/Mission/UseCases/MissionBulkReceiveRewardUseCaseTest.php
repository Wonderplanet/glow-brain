<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Mission\Entities\MissionReceiveRewardStatus;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\UseCases\MissionBulkReceiveRewardUseCase;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstMissionDaily;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use App\Domain\Resource\Mst\Models\MstMissionReward;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\MstUserLevelBonus;
use App\Domain\Resource\Mst\Models\MstUserLevelBonusGroup;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Enums\SaleCondition;
use App\Domain\Shop\Models\UsrConditionPack;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use App\Http\Responses\Data\UsrMissionBonusPointData;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\Support\Traits\TestMissionTrait;
use App\Domain\Shop\Enums\PackType;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use Tests\TestCase;

class MissionBulkReceiveRewardUseCaseTest extends TestCase
{
    use TestMissionTrait;

    private MissionBulkReceiveRewardUseCase $missionBulkReceiveRewardUseCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionBulkReceiveRewardUseCase = $this->app->make(MissionBulkReceiveRewardUseCase::class);
    }

    private function makeMasterData()
    {
        // mst
        // ミッション
        $targetMissionTypes = [
            MissionType::DAILY,
            MissionType::WEEKLY,
            MissionType::BEGINNER,
            MissionType::ACHIEVEMENT,
        ];
        foreach ($targetMissionTypes as $missionType) {
            $mstIdPrefix = $missionType->value;
            $this->createMstMission($missionType, $mstIdPrefix.'1', MissionCriterionType::COIN_COLLECT, null, 10, 'rewardGroup1', 10);
            $this->createMstMission($missionType, $mstIdPrefix.'2', MissionCriterionType::COIN_COLLECT, null, 20, 'rewardGroup2', 20);
            $this->createMstMission($missionType, $mstIdPrefix.'3', MissionCriterionType::COIN_COLLECT, null, 30, 'rewardGroup3', 30);
            $this->createMstMission($missionType, $mstIdPrefix.'10', MissionCriterionType::COIN_COLLECT, null, 100, 'rewardGroup4', 100);
            // ボーナスポイントミッション
            $this->createMstMission($missionType, $mstIdPrefix.'BonusPoint1', MissionCriterionType::MISSION_BONUS_POINT, null, 20, 'rewardGroup4', 0);
            $this->createMstMission($missionType, $mstIdPrefix.'BonusPoint2', MissionCriterionType::MISSION_BONUS_POINT, null, 50, 'rewardGroup4', 0);
            $this->createMstMission($missionType, $mstIdPrefix.'BonusPoint10', MissionCriterionType::MISSION_BONUS_POINT, null, 100, 'rewardGroup4', 0);
        }
        // 報酬設定
        $this->createMstReward('rewardGroup1', RewardType::EXP, null, 100);
        $this->createMstReward('rewardGroup1', RewardType::COIN, null, 200);
        $this->createMstReward('rewardGroup1', RewardType::FREE_DIAMOND, null, 300);
        $this->createMstReward('rewardGroup1', RewardType::ITEM, 'item1', 400);
        $this->createMstReward('rewardGroup1', RewardType::ITEM, 'item2', 500);
        $this->createMstReward('rewardGroup1', RewardType::EMBLEM, 'emblem1', 1);
        $this->createMstReward('rewardGroup2', RewardType::COIN, null, 200);
        $this->createMstReward('rewardGroup3', RewardType::COIN, null, 300);
        $this->createMstReward('rewardGroup4', RewardType::COIN, null, 400);
        // その他
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 10],
            ['level' => 3, 'stamina' => 10, 'exp' => 100],
        ]);
        MstUserLevelBonus::factory()->create([
            'level' => 3,
            'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
        ]);
        MstUserLevelBonusGroup::factory()->createMany([
            [
                'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item1',
                'resource_amount' => 600,
            ],
            [
                'mst_user_level_bonus_group_id' => 'usrLevelBonusGroup1',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item3',
                'resource_amount' => 700,
            ],
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

    private function checkUsrMission(
        ?UsrMissionNormal $usrMission,
        int $status,
        int $progress,
        string $latestResetAt,
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

    public static function params_test_exec_一括受取できる(): array
    {
        return [
            'デイリー' => [MissionType::DAILY],
            'ウィークリー' => [MissionType::WEEKLY],
            '初心者' => [MissionType::BEGINNER],
            'アチーブメント' => [MissionType::ACHIEVEMENT],
        ];
    }

    #[DataProvider('params_test_exec_一括受取できる')]
    public function test_exec_一括受取できる(MissionType $missionType)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-10-18 11:00:00');
        $nowString = $now->toDateTimeString();

        $hasBonusPoint = match ($missionType) {
            MissionType::ACHIEVEMENT => false,
            default => true,
        };

        // mst
        $this->makeMasterData();
        $mstIdPrefix = $missionType->value;

        // usr
        // 一括受け取りでボーナスポイント60獲得
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'mission_type' => $missionType->getIntValue(),
            'status' => MissionStatus::CLEAR->value, // 受取可
            'cleared_at' => $now->toDateTimeString(),
            'received_reward_at' => null,
            'latest_reset_at' => $now,
        ];
        UsrMissionNormal::factory()->createMany([
            ['mst_mission_id' => $mstIdPrefix.'1', 'progress' => 10, ...$recordBase],
            ['mst_mission_id' => $mstIdPrefix.'2', 'progress' => 20, ...$recordBase],
            ['mst_mission_id' => $mstIdPrefix.'3', 'progress' => 30, ...$recordBase],
        ]);

        // 初心者ミッションの進捗判定をするように準備
        $this->prepareUpdateBeginnerMission($usrUserId);

        $this->createDiamond($usrUserId, freeDiamond: 0);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
        ]);

        // Exercise
        $resultData = $this->missionBulkReceiveRewardUseCase->exec(
            new CurrentUser($usrUserId), UserConstant::PLATFORM_IOS, $missionType->value, [
                $mstIdPrefix.'1',
                $mstIdPrefix.'2',
                $mstIdPrefix.'3',
            ]
        );

        // Verify

        // レスポンス確認
        if ($hasBonusPoint) {
            // ボーナスポイントステータス情報確認
            $actuals = $resultData->usrMissionBonusPoints->keyBy(function (UsrMissionBonusPointData $data) {
                return $data->getMissionType();
            });
            $actual = $actuals->get($missionType->value);
            $this->assertNotNull($actual);
            // 配列内の順序関係なく、組み合わせが合っているかどうかの確認
            $this->assertEqualsCanonicalizing([20, 50], $actual->getReceivedRewardPoints()->toArray());

            // 報酬を受け取ったミッションID確認
            $actuals = $resultData->missionReceiveRewardStatuses->map(function (MissionReceiveRewardStatus $data) {
                return $data->getMstMissionId();
            });
            $this->assertEqualsCanonicalizing([$mstIdPrefix.'1', $mstIdPrefix.'2', $mstIdPrefix.'3', $mstIdPrefix.'BonusPoint1', $mstIdPrefix.'BonusPoint2'], $actuals->toArray());

            // 受け取った報酬確認(Rewardクラスインスタンスの数で確認)
            $actuals = $resultData->missionRewards;
            $this->assertCount(10, $actuals);
        } else {
            // 報酬を受け取ったミッションID確認
            $actuals = $resultData->missionReceiveRewardStatuses->map->getMstMissionId();
            $this->assertEqualsCanonicalizing([$mstIdPrefix.'1', $mstIdPrefix.'2', $mstIdPrefix.'3'], $actuals->toArray());

            // 受け取った報酬確認(Rewardクラスインスタンスの数で確認)
            $actuals = $resultData->missionRewards;
            $this->assertCount(8, $actuals);
        }

        // DB確認
        $usrMissions = $this->getMstMissionIdUsrMissionNormalMap($usrUserId, $missionType);
        // 受け取り済みになってることを確認
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'1'), MissionStatus::RECEIVED_REWARD->value, 10, $now, $nowString, $nowString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'2'), MissionStatus::RECEIVED_REWARD->value, 20, $now, $nowString, $nowString);
        $this->checkUsrMission($usrMissions->get($mstIdPrefix.'3'), MissionStatus::RECEIVED_REWARD->value, 30, $now, $nowString, $nowString);
        if ($hasBonusPoint) {
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'BonusPoint1'), MissionStatus::RECEIVED_REWARD->value, 20, $now, $nowString, $nowString);
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'BonusPoint2'), MissionStatus::RECEIVED_REWARD->value, 50, $now, $nowString, $nowString);

            // 未受け取りであることを確認
            $this->checkUsrMission($usrMissions->get($mstIdPrefix.'BonusPoint10'), MissionStatus::UNCLEAR->value, 60, $now, null, null);

            $this->assertCount(7, $usrMissions);
        } else {
            $this->assertCount(4, $usrMissions);
        }
    }

    public function testExec_ユーザーレベルパック開放テスト()
    {
        // NOTE バグ修正確認のためのテスト
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-10-18 11:00:00');

        // mst
        MstMissionDaily::factory()->create([
            'id' => 'daily1',
            'criterion_type' => MissionCriterionType::COIN_COLLECT->value,
            'criterion_value' => null,
            'criterion_count' => 10,
            'mst_mission_reward_group_id' => 'rewardGroup1'
        ]);
        MstMissionReward::factory()->create([
            'group_id' => 'rewardGroup1',
            'resource_type' => RewardType::EXP->value,
            'resource_id' => null,
            'resource_amount' => 10,
        ]);
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'exp' => 0],
            ['level' => 2, 'exp' => 10],
        ]);
        $oprProduct = OprProduct::factory()->create(['product_type' => ProductType::PACK->value])->toEntity();
        $mstPackId = MstPack::factory()->create([
            'product_sub_id' => $oprProduct->getId(),
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition' => SaleCondition::USER_LEVEL->value,
            'sale_condition_value' => '2',
        ])->toEntity()->getId();

        // usr
        UsrMissionNormal::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_mission_id' => 'daily1',
            'mission_type' => MissionType::DAILY->getIntValue(),
            'status' => MissionStatus::CLEAR->value, // 受取可
            'progress' => 10,
            'latest_reset_at' => $now->toDateTimeString(),
            'cleared_at' => $now->toDateTimeString(),
            'received_reward_at' => null,
        ]);

        $this->createDiamond($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0
        ]);

        // Exercise
        $resultData = $this->missionBulkReceiveRewardUseCase->exec(
            new CurrentUser($usrUserId), UserConstant::PLATFORM_IOS, MissionType::DAILY->value, [
                'daily1'
            ]
        );

        // Verify
        $usrConditionPack = $resultData->usrConditionPacks->first();
        $this->assertNotNull($usrConditionPack);
        $this->assertEquals($mstPackId, $usrConditionPack->getMstPackId());

        // DB確認
        $usrConditionPack = UsrConditionPack::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_pack_id', $mstPackId)
            ->first();
        $this->assertNotNull($usrConditionPack);
    }

    public function test_exec_降臨バトル関係のミッションをまとめて一括受け取りできること()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        // mst
        MstMissionLimitedTerm::factory()->createMany([
            /**
             * 受け取れる想定
             */
            // 期間内、降臨バトル関係
            [
                'id' => 'mst_mission_limited_term_id_1',
                'progress_group_key' => 'progress_group_key_1',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE,
                'mst_mission_reward_group_id' => 'rewardGroup3',
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString(),
            ],
            /**
             * 受け取れない想定
             */
            // 期間外、降臨バトル関係
            [
                'id' => 'mst_mission_limited_term_id_2',
                'progress_group_key' => 'progress_group_key_2',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE,
                'mst_mission_reward_group_id' => 'rewardGroup4',
                'start_at' => $now->subHours(2)->toDateTimeString(),
                'end_at' => $now->subHours(1)->toDateTimeString(),
            ],
        ]);
        MstMissionReward::factory()->createMany([
            [
                'group_id' => 'rewardGroup3',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item3',
                'resource_amount' => 300,
            ],
            [
                'group_id' => 'rewardGroup4',
                'resource_type' => RewardType::ITEM->value,
                'resource_id' => 'item4',
                'resource_amount' => 400,
            ],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'item3'],
            ['id' => 'item4'],
        ]);

        // usr
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'status' => MissionStatus::CLEAR, // 受取可
            'is_open' => MissionUnlockStatus::OPEN,
            'cleared_at' => $now->toDateTimeString(),
            'received_reward_at' => null,
            'latest_reset_at' => $now->toDateTimeString(),
        ];
        UsrMissionLimitedTerm::factory()->createMany([
            ['mst_mission_limited_term_id' => 'mst_mission_limited_term_id_1', ...$recordBase],
            ['mst_mission_limited_term_id' => 'mst_mission_limited_term_id_2', ...$recordBase],
        ]);
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item1', 'amount' => 100],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item2', 'amount' => 100],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item3', 'amount' => 100],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'item4', 'amount' => 100],
        ]);
        $this->createDiamond($usrUserId, 0);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
        ]);

        // Exercise
        $resultData = $this->missionBulkReceiveRewardUseCase->exec(
            new CurrentUser($usrUserId),
            UserConstant::PLATFORM_IOS,
            MissionType::LIMITED_TERM->value,
            [
                'mst_mission_limited_term_id_1', // 受け取りできる
                'mst_mission_limited_term_id_2', // 期限切れで受け取りできない
            ],
        );

        // Verify
        // 報酬を受け取ったミッションID確認
        $this->assertEqualsCanonicalizing(
            collect([
                new MissionReceiveRewardStatus(MissionType::LIMITED_TERM, 'mst_mission_limited_term_id_1', null),
                new MissionReceiveRewardStatus(MissionType::LIMITED_TERM, 'mst_mission_limited_term_id_2', UnreceivedRewardReason::INVALID_DATA),
            ]),
            $resultData->missionReceiveRewardStatuses,
        );

        // レスポンスの期間限定ミッションステータスデータ確認（受け取り成功したミッションのみ含まれる）
        $usrMissionStatusDataIds = $resultData->usrMissionlimitedTermStatusDataList->map(function ($data) {
            return $data->getMstMissionId();
        });
        $this->assertContains('mst_mission_limited_term_id_1', $usrMissionStatusDataIds);
        $this->assertCount(1, $resultData->usrMissionlimitedTermStatusDataList);

        // DB確認
        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::LIMITED_TERM);
        $this->checkUsrMissionStatus($usrMissions, 'mst_mission_limited_term_id_1', true, true, $now->toDateTimeString(), true, $now->toDateTimeString());
        $this->checkUsrMissionStatus($usrMissions, 'mst_mission_limited_term_id_2', true, true, $now->toDateTimeString(), false, null);

        $usrItems = UsrItem::where('usr_user_id', $usrUserId)->get()
            ->keyBy(function (UsrItem $usrItem) {
                return $usrItem->getMstItemId();
            });
        $this->assertEquals(100 + 300, $usrItems->get('item3')->getAmount());
        $this->assertEquals(100 + 0, $usrItems->get('item4')->getAmount());
    }

    /**
     * 原画のかけら、原画 がミッション報酬として受け取りできることを確認
     */
    public function test_exec_原画パネルミッションの報酬を一括受け取りできる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getId();

        // mst
        // 2種類の原画とそれぞれのかけらを作成（各原画は3つのかけらで完成）
        MstArtwork::factory()->createMany([
            ['id' => 'artwork_panel_1'],
            ['id' => 'artwork_panel_2'],
        ]);
        MstArtworkFragment::factory()->createMany([
            // artwork_panel_1のかけら（3つで完成）
            ['id' => 'fragment_panel_1_1', 'mst_artwork_id' => 'artwork_panel_1'],
            ['id' => 'fragment_panel_1_2', 'mst_artwork_id' => 'artwork_panel_1'],
            ['id' => 'fragment_panel_1_3', 'mst_artwork_id' => 'artwork_panel_1'],
            // artwork_panel_2のかけら（3つで完成）
            ['id' => 'fragment_panel_2_1', 'mst_artwork_id' => 'artwork_panel_2'],
            ['id' => 'fragment_panel_2_2', 'mst_artwork_id' => 'artwork_panel_2'],
            ['id' => 'fragment_panel_2_3', 'mst_artwork_id' => 'artwork_panel_2'],
        ]);

        // 原画パネルミッション（期間限定ミッション）を作成
        MstMissionLimitedTerm::factory()->createMany([
            [
                'id' => 'artwork_panel_mission_1',
                'progress_group_key' => 'artwork_panel_progress_1',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 10,
                'mission_category' => MissionLimitedTermCategory::ARTWORK_PANEL,
                'mst_mission_reward_group_id' => 'artwork_panel_reward_group_1',
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString(),
            ],
            [
                'id' => 'artwork_panel_mission_2',
                'progress_group_key' => 'artwork_panel_progress_2',
                'criterion_type' => MissionCriterionType::COIN_COLLECT,
                'criterion_value' => null,
                'criterion_count' => 20,
                'mission_category' => MissionLimitedTermCategory::ARTWORK_PANEL,
                'mst_mission_reward_group_id' => 'artwork_panel_reward_group_2',
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString(),
            ],
        ]);

        // 報酬設定
        // mission_1: fragment_panel_1_1を報酬として設定（かけら獲得だけ）
        MstMissionReward::factory()->create([
            'group_id' => 'artwork_panel_reward_group_1',
            'resource_type' => RewardType::ARTWORK_FRAGMENT->value,
            'resource_id' => 'fragment_panel_1_1',
            'resource_amount' => 1,
        ]);
        // mission_2: fragment_panel_2_3を報酬として設定（完成する）
        MstMissionReward::factory()->create([
            'group_id' => 'artwork_panel_reward_group_2',
            'resource_type' => RewardType::ARTWORK_FRAGMENT->value,
            'resource_id' => 'fragment_panel_2_3',
            'resource_amount' => 1,
        ]);

        // usr
        // artwork_panel_2の他のかけらを既にユーザーが持っている（あと1つで完成）
        UsrArtworkFragment::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_panel_2',
                'mst_artwork_fragment_id' => 'fragment_panel_2_1',
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => 'artwork_panel_2',
                'mst_artwork_fragment_id' => 'fragment_panel_2_2',
            ],
        ]);

        // ミッションステータス作成
        $recordBase = [
            'usr_user_id' => $usrUserId,
            'status' => MissionStatus::CLEAR, // 受取可
            'is_open' => MissionUnlockStatus::OPEN,
            'cleared_at' => $now->toDateTimeString(),
            'received_reward_at' => null,
            'latest_reset_at' => $now->toDateTimeString(),
        ];
        UsrMissionLimitedTerm::factory()->createMany([
            ['mst_mission_limited_term_id' => 'artwork_panel_mission_1', ...$recordBase],
            ['mst_mission_limited_term_id' => 'artwork_panel_mission_2', ...$recordBase],
        ]);

        $this->createDiamond($usrUserId, 0);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 0,
        ]);

        // Exercise
        $resultData = $this->missionBulkReceiveRewardUseCase->exec(
            new CurrentUser($usrUserId),
            UserConstant::PLATFORM_IOS,
            MissionType::LIMITED_TERM->value,
            [
                'artwork_panel_mission_1', // かけら獲得だけ
                'artwork_panel_mission_2', // かけら獲得で原画完成
            ],
        );

        // Verify
        // 報酬を受け取ったミッションID確認
        $this->assertEqualsCanonicalizing(
            collect([
                new MissionReceiveRewardStatus(MissionType::LIMITED_TERM, 'artwork_panel_mission_1', null),
                new MissionReceiveRewardStatus(MissionType::LIMITED_TERM, 'artwork_panel_mission_2', null),
            ]),
            $resultData->missionReceiveRewardStatuses,
        );

        // レスポンスの期間限定ミッションステータスデータ確認
        $usrMissionStatusDataIds = $resultData->usrMissionlimitedTermStatusDataList->map(function ($data) {
            return $data->getMstMissionId();
        });
        $this->assertContains('artwork_panel_mission_1', $usrMissionStatusDataIds);
        $this->assertContains('artwork_panel_mission_2', $usrMissionStatusDataIds);

        // レスポンスの原画のかけら確認
        $fragmentIds = $resultData->usrArtworkFragments->map(function ($fragment) {
            return $fragment->getMstArtworkFragmentId();
        });
        // fragment_panel_1_1とfragment_panel_2_3が含まれていることを確認
        $this->assertContains('fragment_panel_1_1', $fragmentIds);
        $this->assertContains('fragment_panel_2_3', $fragmentIds);

        // レスポンスの原画確認（artwork_panel_2が完成している）
        $artworkIds = $resultData->usrArtworks->map(function ($artwork) {
            return $artwork->getMstArtworkId();
        });
        $this->assertContains('artwork_panel_2', $artworkIds);
        $this->assertCount(1, $resultData->usrArtworks);

        // DB確認
        $usrMissions = $this->getUsrMissions($usrUserId, MissionType::LIMITED_TERM);
        $this->checkUsrMissionStatus($usrMissions, 'artwork_panel_mission_1', true, true, $now->toDateTimeString(), true, $now->toDateTimeString());
        $this->checkUsrMissionStatus($usrMissions, 'artwork_panel_mission_2', true, true, $now->toDateTimeString(), true, $now->toDateTimeString());

        // かけら確認
        $usrFragments = UsrArtworkFragment::where('usr_user_id', $usrUserId)->get()
            ->keyBy(function ($fragment) {
                return $fragment->getMstArtworkFragmentId();
            });
        $this->assertNotNull($usrFragments->get('fragment_panel_1_1'));
        $this->assertNotNull($usrFragments->get('fragment_panel_2_1'));
        $this->assertNotNull($usrFragments->get('fragment_panel_2_2'));
        $this->assertNotNull($usrFragments->get('fragment_panel_2_3'));

        // 原画確認（artwork_panel_2が完成している）
        $usrArtwork = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_panel_2')
            ->first();
        $this->assertNotNull($usrArtwork);

        // artwork_panel_1は未完成（かけら1つだけなので）
        $usrArtwork1 = UsrArtwork::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_artwork_id', 'artwork_panel_1')
            ->first();
        $this->assertNull($usrArtwork1);
    }

}
