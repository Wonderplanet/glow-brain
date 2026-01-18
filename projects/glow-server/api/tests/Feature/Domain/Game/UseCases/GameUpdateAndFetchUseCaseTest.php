<?php

declare(strict_types=1);

namespace Feature\Domain\Game\UseCases;
use App\Domain\Common\Enums\Language;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\Game\UseCases\GameUpdateAndFetchUseCase;
use App\Domain\InGame\Enums\InGameSpecialRuleType;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Models\MstEvent;
use App\Domain\Resource\Mst\Models\MstInGameSpecialRule;
use App\Domain\Resource\Mst\Models\MstMissionAchievement;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEvent;
use App\Domain\Resource\Mst\Models\MstMissionEventDaily;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstStageEventSetting;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaUpper;
use App\Domain\Message\Models\Eloquent\UsrMessage;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Domain\Resource\Mst\Models\MstShopPass;
use App\Domain\Resource\Mst\Models\MstShopPassI18n;
use App\Domain\Resource\Mst\Models\MstShopPassReward;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\Shop\Enums\PassRewardType;
use App\Domain\Resource\Enums\RewardType;

use App\Http\Responses\ResultData\GameUpdateAndFetchResultData;
use Carbon\CarbonImmutable;
use Tests\Support\Entities\CurrentUser;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\InGame\Models\UsrEnemyDiscovery;
use App\Domain\Message\Services\UsrMessageService;
use App\Domain\Tutorial\Enums\TutorialFunctionName;

class GameUpdateAndFetchUseCaseTest extends TestCase
{
    use TestMissionTrait;

    private GameUpdateAndFetchUseCase $gameUpdateAndFetchUseCase;
    private UsrMessageRepository $usrMessageRepository;
    private UsrMessageService $usrMessageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usrMessageRepository = app()->make(UsrMessageRepository::class);
        $this->gameUpdateAndFetchUseCase = app(GameUpdateAndFetchUseCase::class);
        $this->usrMessageService = app(UsrMessageService::class);
    }

    public function test_exec_updateでクリア判定になったミッションがfetchのバッジに含まれることを確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ])->getId();
        $user = new CurrentUser($usrUserId);
        $now = $this->fixTime();

        $this->createTestData($usrUserId, $now);

        // mst
        // デイリーボーナスはログイン時に自動受け取りされるのでバッジには含まれない
        MstMissionDailyBonus::factory()->createMany([
            ['id' => 'dailyBonus1', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 1],
            ['id' => 'dailyBonus2', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 2],
            ['id' => 'dailyBonus3', 'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS, 'login_day_count' => 3],
        ]);
        MstMissionAchievement::factory()->createMany([
            ['id' => 'achievement1', 'criterion_type' => MissionCriterionType::LOGIN_COUNT, 'criterion_count' => 1],
            ['id' => 'achievement2', 'criterion_type' => MissionCriterionType::LOGIN_COUNT, 'criterion_count' => 2],
            ['id' => 'achievement3', 'criterion_type' => MissionCriterionType::LOGIN_COUNT, 'criterion_count' => 3],
        ]);

        UsrMissionDailyBonus::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'dailyBonus1',
                'status' => MissionStatus::RECEIVED_REWARD,
                'cleared_at' => $now->toDateTimeString(),
                'received_reward_at' => $now->toDateTimeString(),
            ],
            [
                'usr_user_id' => $usrUserId,
                'mst_mission_daily_bonus_id' => 'dailyBonus2',
                'status' => MissionStatus::RECEIVED_REWARD,
                'cleared_at' => $now->toDateTimeString(),
                'received_reward_at' => null,
            ],
            // 3日目ログインのユーザーデータは用意しない
        ]);
        // achievement
        $this->createUsrMissionNormal($usrUserId, MissionType::ACHIEVEMENT, 'achievement1', MissionStatus::RECEIVED_REWARD, 1, $now, $now, $now);
        $this->createUsrMissionNormal($usrUserId, MissionType::ACHIEVEMENT, 'achievement2', MissionStatus::CLEAR, 2, $now, null, $now);
        $this->createUsrMissionNormal($usrUserId, MissionType::ACHIEVEMENT, 'achievement3', MissionStatus::UNCLEAR, 1, null, null, $now);

        MstEvent::factory()->create([
            'id' => 'mst_event_id'
        ]);
        MstMissionEvent::factory()->createMany([
            [
                'id' => 'event1',
                'criterion_type' => MissionCriterionType::LOGIN_COUNT,
                'criterion_value' => null,
                'criterion_count' => 1,
                'mst_event_id' => 'mst_event_id',
                'event_category' => null
            ],
        ]);
        MstMissionEventDaily::factory()->createMany([
            [
                'id' => 'event_daily1',
                'criterion_type' => MissionCriterionType::LOGIN_COUNT,
                'criterion_value' => null,
                'criterion_count' => 1,
                'mst_event_id' => 'mst_event_id'
            ],
        ]);

        // usr
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            // 連続ログインでログインカウントが増えるように設定
            'last_login_at' => $now->copy()->subDay()->toDateTimeString(),
            // usecase実行で+1され、3になり、dailyBonus3,achievement3がクリアとなり
            // dailyBonus3は自動受け取りされ、achievement3は未受け取り状態となりバッジ+1になる
            'login_day_count' => 2,
        ]);
        // Exercise
        $result = $this->gameUpdateAndFetchUseCase->exec($user, Language::Ja->value, UserConstant::PLATFORM_IOS, '');

        // Verify
        $this->assertInstanceOf(GameUpdateAndFetchResultData::class, $result);
        $this->assertEquals(1, $result->gameFetchData->gameBadgeData->unreceivedMissionRewardCount);
        $this->assertEquals(2, $result->gameFetchData->gameBadgeData->unreceivedMissionEventRewardCounts->sum(fn($value) => $value)); // event1, event_daily1
    }

    public function test_exec_期間内のユーザーガシャ情報のみが含まれること()
    {
        // Setup
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ])->getId();
        $user = new CurrentUser($usrUserId);
        $now = $this->fixTime();
        $this->createTestData($usrUserId, $now);

        // mst
        OprGacha::factory()->createMany([
            [
                'id'          => 'gachaIdInPeriod1',
                'upper_group' => 'gachaIdInPeriodUpperGroup1',
                'start_at'    => $now->subDays(1)->toDateTimeString(),
                'end_at'      => $now->addDays(1)->toDateTimeString(),
            ],// 期間内、天井あり
            [
                'id'          => 'gachaIdInPeriod2',
                'upper_group' => 'None',
                'start_at'    => $now->subDays(1)->toDateTimeString(),
                'end_at'      => $now->addDays(1)->toDateTimeString(),
            ],// 期間内、天井なし
            [
                'id'          => 'gachaIdOutPeriod1',
                'upper_group' => 'gachaIdOutPeriodUpperGroup1',
                'start_at'    => $now->subDays(2)->toDateTimeString(),
                'end_at'      => $now->subDays(1)->toDateTimeString(),
            ],// 期間外(過去開催)
            [
                'id'          => 'gachaIdOutPeriod2',
                'upper_group' => 'gachaIdOutPeriodUpperGroup2',
                'start_at'    => $now->addDays(1)->toDateTimeString(),
                'end_at'      => $now->addDays(2)->toDateTimeString(),
            ],// 期間外(未来開催※過去に同天井グループの天井カウントしている想定)
        ]);
        OprGachaUpper::factory()->createMany([
            [
                'upper_group' => 'gachaIdInPeriodUpperGroup1',
                'upper_type'  => 'MaxRarity',
                'count'       => 100,
            ],
            [
                'upper_group' => 'gachaIdOutPeriodUpperGroup1',
                'upper_type'  => 'MaxRarity',
                'count'       => 100,
            ],
            [
                'upper_group' => 'gachaIdOutPeriodUpperGroup2',
                'upper_type'  => 'MaxRarity',
                'count'       => 100,
            ],
        ]);

        // usr
        UsrGacha::factory()->createMany([
            [
                'usr_user_id'  => $user->getId(),
                'opr_gacha_id' => 'gachaIdInPeriod1',
            ],
            [
                'usr_user_id'  => $user->getId(),
                'opr_gacha_id' => 'gachaIdInPeriod2',
            ],
            [
                'usr_user_id'  => $user->getId(),
                'opr_gacha_id' => 'gachaIdOutPeriod1',
            ],
            [
                'usr_user_id'  => $user->getId(),
                'opr_gacha_id' => 'gachaIdOutPeriod2',
            ],
        ]);
        UsrGachaUpper::factory()->createMany([
            [
                'usr_user_id' => $user->getId(),
                'upper_group' => 'gachaIdInPeriodUpperGroup1'
            ],
            [
                'usr_user_id' => $user->getId(),
                'upper_group' => 'gachaIdOutPeriodUpperGroup1'
            ],
            [
                'usr_user_id' => $user->getId(),
                'upper_group' => 'gachaIdOutPeriodUpperGroup2'
            ],
        ]);

        // Exercise
        $result = $this->gameUpdateAndFetchUseCase->exec($user, Language::Ja->value, UserConstant::PLATFORM_IOS, '');

        // Verify
        $this->assertInstanceOf(GameUpdateAndFetchResultData::class, $result);
        $this->assertCount(2, $result->gameFetchOtherData->usrGachas);
        $usrGachaIds = $result->gameFetchOtherData->usrGachas->map(function ($entity) {
            return $entity->getOprGachaId();
        });
        $this->assertContains('gachaIdInPeriod1', $usrGachaIds);
        $this->assertContains('gachaIdInPeriod2', $usrGachaIds);

        $this->assertCount(1, $result->gameFetchOtherData->usrGachaUppers);
        $usrUpperGroups = $result->gameFetchOtherData->usrGachaUppers->map(function ($entity) {
            return $entity->getUpperGroup();
        });
        $this->assertContains('gachaIdInPeriodUpperGroup1', $usrUpperGroups);
    }

    public function test_exec_日付を跨いで取得したガシャのデイリー回数がリセットされていること()
    {
        // Setup
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ])->getId();
        $user = new CurrentUser($usrUserId);
        $now = $this->fixTime();
        $this->createTestData($usrUserId, $now);

        // mst
        OprGacha::factory()->createMany([
            // リセット対象になるガシャ
            [
                'id'          => 'opr_gacha_id_1',
                'upper_group' => 'None',
            ],
            // リセット対象にならないガシャ
            [
                'id'          => 'opr_gacha_id_2',
                'upper_group' => 'None',
            ],
        ]);

        // usr
        UsrGacha::factory()->createMany([
            [
                'usr_user_id'  => $user->getId(),
                'opr_gacha_id' => 'opr_gacha_id_1',
                'ad_played_at' => $now->subDays(1)->toDateTimeString(),
                'played_at' => $now->subDays(1)->toDateTimeString(),
                'ad_count' => 100,
                'ad_daily_count' => 100,
                'count' => 100,
                'daily_count' => 100,
            ],
            [
                'usr_user_id'  => $user->getId(),
                'opr_gacha_id' => 'opr_gacha_id_2',
                'ad_played_at' => $now->toDateTimeString(),
                'played_at' => $now->toDateTimeString(),
                'ad_count' => 100,
                'ad_daily_count' => 100,
                'count' => 100,
                'daily_count' => 100,
            ],
        ]);

        // Exercise
        $result = $this->gameUpdateAndFetchUseCase->exec($user, Language::Ja->value, UserConstant::PLATFORM_IOS, '');

        // Verify
        $this->assertInstanceOf(GameUpdateAndFetchResultData::class, $result);
        $usrGachas = $result->gameFetchOtherData->usrGachas->keyBy(function ($entity) {
            return $entity->getOprGachaId();
        });
        /** @var \App\Domain\Gacha\Models\UsrGachaInterface|null $usrGacha */
        $usrGacha = $usrGachas->get('opr_gacha_id_1');
        $this->assertNotNull($usrGacha);
        $this->assertEquals(100, $usrGacha->getAdCount());
        $this->assertEquals(0, $usrGacha->getAdDailyCount());
        $this->assertEquals(100, $usrGacha->getCount());
        $this->assertEquals(0, $usrGacha->getDailyCount());

        /** @var \App\Domain\Gacha\Models\UsrGachaInterface|null $usrGacha */
        $usrGacha = $usrGachas->get('opr_gacha_id_2');
        $this->assertNotNull($usrGacha);
        $this->assertEquals(100, $usrGacha->getAdCount());
        $this->assertEquals(100, $usrGacha->getAdDailyCount());
        $this->assertEquals(100, $usrGacha->getCount());
        $this->assertEquals(100, $usrGacha->getDailyCount());

    }

    public function test_exec_スピードアタックが正しく含まれること()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ])->getId();
        $user = new CurrentUser($usrUserId);
        $this->createTestData($usrUserId, $now);
        UsrStageEvent::factory()->createMany([
            // 復刻扱いにならない
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => 'mstStageId1',
                'clear_count' => 1,
                'reset_clear_time_ms' => 20000,
                'clear_time_ms' => 10000,
                'latest_event_setting_end_at' => $now->toDateTimeString(),
            ],
            // 復刻扱いになる
            [
                'usr_user_id' => $usrUserId,
                'mst_stage_id' => 'mstStageId2',
                'clear_count' => 1,
                'reset_clear_time_ms' => 20000,
                'clear_time_ms' => 10000,
                'latest_event_setting_end_at' => $now->subDays(2)->toDateTimeString(),
            ],
        ]);

        MstQuest::factory()->createMany([
            [
                'id' => 'mstQuestId1',
                'quest_type' => 'event',
                'start_date' => $now->subDays(1)->toDateTimeString(),
                'end_date' => $now->addDays(1)->toDateTimeString(),
            ],
            [
                'id' => 'mstQuestId2',
                'quest_type' => 'event',
                'start_date' => $now->subDays(1)->toDateTimeString(),
                'end_date' => $now->addDays(1)->toDateTimeString(),
            ],
        ]);
        MstStage::factory()->createMany([
            [
                'id' => 'mstStageId1',
                'mst_quest_id' => 'mstQuestId1',
            ],
            [
                'id' => 'mstStageId2',
                'mst_quest_id' => 'mstQuestId2',
            ],
        ]);
        MstStageEventSetting::factory()->createMany([
            [
                'mst_stage_id' => 'mstStageId1',
                'clearable_count' => 10,
                'ad_challenge_count' => 10,
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDays(1)->toDateTimeString(),
            ],
            [
                'mst_stage_id' => 'mstStageId2',
                'clearable_count' => 10,
                'ad_challenge_count' => 10,
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDays(1)->toDateTimeString(),
            ],
        ]);

        MstInGameSpecialRule::factory()->createMany([
            [
                'content_type' => InGameContentType::STAGE,
                'target_id' => 'mstStageId1',
                'rule_type' => InGameSpecialRuleType::SPEED_ATTACK,
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDays(1)->toDateTimeString(),
            ],
            [
                'content_type' => InGameContentType::STAGE,
                'target_id' => 'mstStageId2',
                'rule_type' => InGameSpecialRuleType::SPEED_ATTACK,
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDays(1)->toDateTimeString(),
            ],
        ]);

        // Exercise
        $result = $this->gameUpdateAndFetchUseCase->exec($user, Language::Ja->value, UserConstant::PLATFORM_IOS, '');

        // Verify
        $usrStageEvents = $result->gameFetchData->usrStageEvents->keyBy(function ($entity) {
            return $entity->getMstStageId();
        });
        /** @var \App\Domain\Stage\Models\UsrStageEventInterface|null $usrStageEvent */
        $usrStageEvent = $usrStageEvents->get('mstStageId1');
        $this->assertNotNull($usrStageEvent);
        $this->assertEquals(20000, $usrStageEvent->getResetClearTimeMs());
        $this->assertEquals(10000, $usrStageEvent->getClearTimeMs());

        /** @var \App\Domain\Stage\Models\UsrStageEventInterface|null $usrStageEvent */
        $usrStageEvent = $usrStageEvents->get('mstStageId2');
        $this->assertNotNull($usrStageEvent);
        $this->assertNull($usrStageEvent->getResetClearTimeMs());
        $this->assertEquals(10000, $usrStageEvent->getClearTimeMs());
    }

    public function test_exec_パスリワードが日跨ぎで正常に更新される()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ])->getId();
        $user = new CurrentUser($usrUserId);
        $this->createTestData($usrUserId, $now);

        $productSubId = 'pack_160_1_framework'; // opr_products.id
        $productSubId2 = 'pack_160_2_framework'; // opr_products.id
        $mstPass = MstShopPass::factory()->create([
            'opr_product_id' => $productSubId,
            'pass_duration_days' => 7,
        ])->toEntity();

        $mstPass2 = MstShopPass::factory()->create([
            'opr_product_id' => $productSubId2,
            'pass_duration_days' => 7,
        ])->toEntity();

        MstShopPassI18n::factory()->createMany([
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'name' => 'テストパス',
            ],
            [
                'mst_shop_pass_id' => $mstPass2->getId(),
                'name' => 'テストパス2',
            ],
        ]);

        MstShopPassReward::factory()->createMany([
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'pass_reward_type' => PassRewardType::DAILY->value,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 50,
            ],
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'pass_reward_type' => PassRewardType::DAILY->value,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
            [
                'mst_shop_pass_id' => $mstPass2->getId(),
                'pass_reward_type' => PassRewardType::DAILY->value,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);

        UsrShopPass::factory()->createMany([
            [
                'id' => 'test',
                'usr_user_id' => $usrUserId,
                'mst_shop_pass_id' => $mstPass->getId(),
                'daily_reward_received_count' => 1,
                'daily_latest_received_at' => $now->subDays(1)->toDateTimeString(),
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDay(7)->toDateTimeString(),
            ],
            [
                'id' => 'test2',
                'usr_user_id' => $usrUserId,
                'mst_shop_pass_id' => $mstPass2->getId(),
                'daily_reward_received_count' => 1,
                'daily_latest_received_at' => $now->subDays(1)->toDateTimeString(),
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->addDay(7)->toDateTimeString(),
            ],
        ]);

        // 更新前のショップパスデータ
        $beforeUsrShopPass = UsrShopPass::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_shop_pass_id', $mstPass->getId())
            ->first();

        // 更新前のショップパスデータ2
        $beforeUsrShopPass2 = UsrShopPass::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_shop_pass_id', $mstPass2->getId())
            ->first();

        // Exercise
        $this->gameUpdateAndFetchUseCase->exec($user, Language::Ja->value, UserConstant::PLATFORM_IOS, '');

        // 更新後のショップパスデータ
        $afterUsrShopPass = UsrShopPass::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_shop_pass_id', $mstPass->getId())
            ->first();

        // 更新後のショップパスデータ2
        $afterUsrShopPass2 = UsrShopPass::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_shop_pass_id', $mstPass2->getId())
            ->first();

        // リワード送信したメッセージのカウントチェック
        $usrMessageResult = $this->usrMessageService->getMessageData($usrUserId, $now, Language::Ja->value);

        //  メッセージが3件登録されている（３件あるがグルーピング設定のため、返却はは2件）
        $this->assertCount(2, $usrMessageResult);

        // カウントが更新されている
        $this->assertEquals(($beforeUsrShopPass->daily_reward_received_count + 1), $afterUsrShopPass->daily_reward_received_count);
        // 日付が更新されている
        $this->assertEquals($now->toDateTimeString(), $afterUsrShopPass->daily_latest_received_at);

        // カウントが更新されている2
        $this->assertEquals(($beforeUsrShopPass2->daily_reward_received_count + 1), $afterUsrShopPass2->daily_reward_received_count);
        // 日付が更新されている2
        $this->assertEquals($now->toDateTimeString(), $afterUsrShopPass2->daily_latest_received_at);
    }

    public function test_exec_パスリワードが受取済みであればデータ更新されない()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser([
            'tutorial_status' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ])->getId();
        $user = new CurrentUser($usrUserId);
        $this->createTestData($usrUserId, $now);

        $productSubId = 'pack_160_1_framework'; // opr_products.id

        $mstPass = MstShopPass::factory()->create([
            'opr_product_id' => $productSubId,
            'pass_duration_days' => 7,
        ])->toEntity();
        MstShopPassI18n::factory()->create([
            'mst_shop_pass_id' => $mstPass->getId(),
            'name' => 'テストパス',
        ]);
        MstShopPassReward::factory()->createMany([
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'pass_reward_type' => PassRewardType::DAILY->value,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 50,
            ],
            [
                'mst_shop_pass_id' => $mstPass->getId(),
                'pass_reward_type' => PassRewardType::DAILY->value,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
        ]);

        UsrShopPass::factory()->create([
            'id' => 'test',
            'usr_user_id' => $usrUserId,
            'mst_shop_pass_id' => $mstPass->getId(),
            'daily_reward_received_count' => 1,
            'daily_latest_received_at' => $now->toDateTimeString(),
            'start_at' => $now->subDays(1)->toDateTimeString(),
            'end_at' => $now->addDay(7)->toDateTimeString(),
        ]);

        // 更新前のショップパスデータ
        $beforeUsrShopPass = UsrShopPass::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_shop_pass_id', $mstPass->getId())
            ->first();

        // Exercise
        $this->gameUpdateAndFetchUseCase->exec($user, Language::Ja->value, UserConstant::PLATFORM_IOS, '');

        // 更新後のショップパスデータ
        $afterUsrShopPass = UsrShopPass::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_shop_pass_id', $mstPass->getId())
            ->first();

        // リワードメッセージが登録されていない
        $usrMessageResult = $this->usrMessageRepository->getReceivableList($usrUserId, $now);
        $this->assertCount(0, $usrMessageResult);

        // カウントが更新されていない
        $this->assertEquals($beforeUsrShopPass->daily_reward_received_count, $afterUsrShopPass->daily_reward_received_count);
        // 日付が更新されてない
        $this->assertEquals($beforeUsrShopPass->daily_latest_received_at, $afterUsrShopPass->daily_latest_received_at);
    }

    private function createTestData(string $usrUserId, CarbonImmutable $now)
    {
        // mst
        MstUserLevel::factory()->create([
            'level' => 1,
        ]);

        // usr
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
        ]);
        $this->createDiamond($usrUserId);

        UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
            'paid_price' => 0,
            'renotify_at' => null,
        ]);
    }
}
