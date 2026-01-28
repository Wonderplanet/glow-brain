<?php

namespace Tests\Feature\Domain\Mission\Criteria;

use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\UseCases\AdventBattleEndUseCase;
use App\Domain\AdventBattle\UseCases\AdventBattleStartUseCase;
use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Auth\Services\AccessTokenService;
use App\Domain\Common\Enums\Language;
use App\Domain\Emblem\Constants\EmblemConstant;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Encyclopedia\Constants\EncyclopediaConstant;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\UseCases\GachaDrawUseCase;
use App\Domain\Game\Services\GameService;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\IdleIncentive\UseCases\IdleIncentiveQuickReceiveByAdUseCase;
use App\Domain\IdleIncentive\UseCases\IdleIncentiveQuickReceiveByDiamondUseCase;
use App\Domain\IdleIncentive\UseCases\IdleIncentiveReceiveUseCase;
use App\Domain\InGame\Models\UsrEnemyDiscovery;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Services\UsrItemService;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Outpost\Enums\OutpostEnhancementType;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Outpost\UseCases\OutpostEnhanceUseCase;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Domain\Resource\Mst\Models\MstEmblem;
use App\Domain\Resource\Mst\Models\MstEnemyCharacter;
use App\Domain\Resource\Mst\Models\MstIdleIncentive;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveItem;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstOutpost;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Resource\Mst\Models\MstStage;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Domain\Reward\Managers\RewardManager;
use App\Domain\Reward\Services\RewardSendService;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\UseCases\ShopTradeShopItemUseCase;
use App\Domain\Stage\Enums\QuestType;
use App\Domain\Stage\Enums\StageAutoLapType;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\UseCases\StageEndUseCase;
use App\Domain\Stage\UseCases\StageStartUseCase;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Unit\Enums\UnitColorType;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Services\UnitGradeUpService;
use App\Domain\Unit\Services\UnitLevelUpService;
use App\Domain\Unit\Services\UnitRankUpService;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Services\UserAccountLinkService;
use App\Domain\User\Services\UserService;
use App\Domain\User\UseCases\UserBuyStaminaAdUseCase;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\Support\Entities\CurrentUser;
use Tests\Support\Entities\TestLogTrigger;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * ServiceやUseCaseを実行して、ミッションのトリガーのされ方が正しいことを確認するテスト
 * MissionCriterionを使って、トリガーされた進捗値を集約し、新規の進捗値が想定する値になっているか確認する
 *
 * ミッション進捗管理のテストは、MissionXXXUpdateServiceTestで行っているため、
 * ここでは、トリガーと進捗値集約のテストを行う
 *
 * Criterionごとの特有のケースをテストしたい場合は、別途CriterionTestファイルを作成してテストする
 */
class MissionAllCriterionTest extends TestCase
{
    use TestMissionTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // mockして進捗判定を進めないようにする
        // useCaseテストを使ったテストの場合、UseCaseTraitでのミッション進捗更新が実行されMissionManagerに入ったトリガーが消えてしまうため
        $this->mockExecHandleAllUpdateTriggeredMissions();
    }

    private function createMstData()
    {
        /**
         * ゲームドメイン系
         */

        /**
         * ユニット系
         */
        $unitLabel = 'DropR';
        MstUnit::factory()->createMany([
            ['id' => 'unit1', 'unit_label' => $unitLabel, 'fragment_mst_item_id' => 'item1', 'mst_series_id' => 'series1',],
            ['id' => 'unit2', 'unit_label' => $unitLabel, 'fragment_mst_item_id' => 'item1', 'mst_series_id' => 'series1',],
            ['id' => 'unit3', 'unit_label' => $unitLabel, 'fragment_mst_item_id' => 'item1', 'mst_series_id' => 'series2',],
            ['id' => 'unit4', 'unit_label' => $unitLabel, 'fragment_mst_item_id' => 'item1', 'mst_series_id' => 'series3',],
        ]);
        MstUnitLevelUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'level' => 1, 'required_coin' => 1,],
            ['unit_label' => $unitLabel, 'level' => 2, 'required_coin' => 2,],
            ['unit_label' => $unitLabel, 'level' => 3, 'required_coin' => 3,],
            ['unit_label' => $unitLabel, 'level' => 4, 'required_coin' => 4,],
            ['unit_label' => $unitLabel, 'level' => 5, 'required_coin' => 5,],
        ]);
        MstUnitGradeUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 0, 'require_amount' => 1,],
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'require_amount' => 2,],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'require_amount' => 3,],
            ['unit_label' => $unitLabel, 'grade_level' => 3, 'require_amount' => 4,],
        ]);
        MstUnitRankUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'rank' => 1, 'require_level' => 1, 'amount' => 1,],
            ['unit_label' => $unitLabel, 'rank' => 2, 'require_level' => 2, 'amount' => 1,],
            ['unit_label' => $unitLabel, 'rank' => 3, 'require_level' => 3, 'amount' => 1,],
            ['unit_label' => $unitLabel, 'rank' => 4, 'require_level' => 4, 'amount' => 1,],
        ]);
        MstUnitFragmentConvert::factory()->createMany([
            ['unit_label' => $unitLabel, 'convert_amount' => 3],
        ]);

        /**
         * ステージ系
         */
        MstQuest::factory()->createMany([
            ['id' => 'quest1', 'quest_type' => QuestType::NORMAL, 'start_date' => '2023-01-01 00:00:00', 'end_date' => '2037-01-01 00:00:00',]
        ]);
        MstStage::factory()->createMany([
            [
                'id' => 'stage1',
                'mst_quest_id' => 'quest1',
                'prev_mst_stage_id' => null,
                'mst_artwork_fragment_drop_group_id' => 'artworkFragmentDrop1',
                'auto_lap_type' => StageAutoLapType::INITIAL->value,
                'max_auto_lap_count' => 10,
            ],
            [
                'id' => 'stage2',
                'mst_quest_id' => 'quest1',
                'prev_mst_stage_id' => null,
            ],
        ]);
        // 原画ドロップ
        MstArtwork::factory()->createMany([
            ['id' => 'artwork1', 'mst_series_id' => 'series1'],
            ['id' => 'artwork2', 'mst_series_id' => 'series1'],
            ['id' => 'artwork3', 'mst_series_id' => 'series2'],
            ['id' => 'artwork4', 'mst_series_id' => 'series3'],
        ]);
        MstArtworkFragment::factory()->createMany([
            ['id' => 'artworkFragment1-1', 'mst_artwork_id' => 'artwork1', 'drop_group_id' => 'artworkFragmentDrop1', 'drop_percentage' => 100,],
            ['id' => 'artworkFragment1-2', 'mst_artwork_id' => 'artwork1', 'drop_group_id' => 'artworkFragmentDrop1', 'drop_percentage' => 100,],
            ['id' => 'artworkFragment2-1', 'mst_artwork_id' => 'artwork2', 'drop_group_id' => 'artworkFragmentDrop1', 'drop_percentage' => 100,],
            ['id' => 'artworkFragment3-1', 'mst_artwork_id' => 'artwork3', 'drop_group_id' => 'artworkFragmentDrop1', 'drop_percentage' => 100,],
            ['id' => 'artworkFragment4-1', 'mst_artwork_id' => 'artwork4', 'drop_group_id' => 'artworkFragmentDrop1', 'drop_percentage' => 100,],
        ]);

        /**
         * インゲーム
         */
        MstEnemyCharacter::factory()->createMany([
            // series1
            ['id' => 'enemy1-1', 'mst_series_id' => 'series1',],
            ['id' => 'enemy1-2', 'mst_series_id' => 'series1',],
            ['id' => 'bossEnemy1', 'mst_series_id' => 'series1',],
            // series2
            ['id' => 'enemy2-1', 'mst_series_id' => 'series2',],
            ['id' => 'enemy2-2', 'mst_series_id' => 'series2',],
            ['id' => 'enemy2-3', 'mst_series_id' => 'series2',],
            ['id' => 'enemy2-4', 'mst_series_id' => 'series2',],
            ['id' => 'bossEnemy2', 'mst_series_id' => 'series2',],

        ]);

        /**
         * アイテム系
         */
        MstItem::factory()->createMany([
            ['id' => 'item1', ],
            ['id' => 'item2', ],
            ['id' => 'item3', ],
            ['id' => 'item4', ],
            ['id' => 'confumeItemId1', 'start_date' => '2000-01-01 00:00:00', 'end_date' => '2035-12-31 23:59:59'],
            ['id' => 'rewardItem1',    'start_date' => '2000-01-01 00:00:00', 'end_date' => '2035-12-31 23:59:59'],
            ['id' => 'rankUpMaterial', 'type' => ItemType::RANK_UP_MATERIAL->value, 'effect_value' => UnitColorType::COLORLESS->value, 'start_date' => '2000-01-01 00:00:00', 'end_date' => '2035-12-31 23:59:59']
        ]);
        MstEmblem::factory()->createMany([
            ['id' => 'emblem1', 'mst_series_id' => 'series1',],
            ['id' => 'emblem2', 'mst_series_id' => 'series1',],
            ['id' => 'emblem3', 'mst_series_id' => 'series2',],
            ['id' => 'emblem4', 'mst_series_id' => 'series3',],
        ]);

        /**
         * ショップ系
         */
        MstShopItem::factory()->createMany([
            [
                'id' => 'shopItem1',
                'shop_type' => ShopType::COIN->value,
                'cost_type' => ShopItemCostType::AD->value,
                'is_first_time_free' => 0,
                'tradable_count' => 100,
                'start_date' => '2000-01-01 00:00:00',
                'end_date' => '2030-01-01 00:00:00',
            ],
            [
                'id' => 'shopItem2',
                'shop_type' => ShopType::COIN->value,
                'cost_type' => ShopItemCostType::AD->value,
                'is_first_time_free' => 1,
                'tradable_count' => 100,
                'start_date' => '2000-01-01 00:00:00',
                'end_date' => '2030-01-01 00:00:00',
            ],
        ]);

        /**
         * ガシャ系
         */
        OprGacha::factory()->create([
            'id' => 'gacha1',
            'gacha_type' => GachaType::PREMIUM->value,
            'enable_ad_play' => true,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prizeGroup1',
            'ad_play_interval_time' => 1,
            'daily_play_limit_count' => 30,
            'total_play_limit_count' => 100,
            'daily_ad_limit_count' => 3,
            'total_ad_limit_count' => 10,
        ]);
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => 'gacha1',
            'language' => Language::Ja->value,
        ]);
        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'gacha1',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 10,
                'draw_count' => 10,
                'cost_priority' => 1,
            ],
        ]);
        OprGachaPrize::factory()->createMany([
            ['group_id' => 'prizeGroup1', 'resource_type' => RewardType::UNIT, 'resource_id' => 'unit1'],
            ['group_id' => 'prizeGroup1', 'resource_type' => RewardType::UNIT, 'resource_id' => 'unit2'],
        ]);

        /**
         * ユーザ系
         */
        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 20, 'exp' => 100],
            ['level' => 3, 'stamina' => 30, 'exp' => 200],
        ]);

        /**
         * ゲート系
         */
        MstOutpost::factory()->create([
            'id' => 'outpost1', 'start_at' => '2021-01-01 00:00:00', 'end_at' => '2037-01-01 00:00:00',
        ]);
        MstOutpostEnhancement::factory()->createMany([
            ['id' => 'enhancement1', 'mst_outpost_id' => 'outpost1', 'outpost_enhancement_type' => OutpostEnhancementType::cases()[0]->value],
            ['id' => 'enhancement2', 'mst_outpost_id' => 'outpost1', 'outpost_enhancement_type' => OutpostEnhancementType::cases()[1]->value],
        ]);
        MstOutpostEnhancementLevel::factory()->createMany([
            // enhancement1
            ['mst_outpost_enhancement_id' => 'enhancement1', 'level' => 1, 'cost_coin' => 0, 'enhancement_value' => 100],
            ['mst_outpost_enhancement_id' => 'enhancement1', 'level' => 2, 'cost_coin' => 200, 'enhancement_value' => 200],
            ['mst_outpost_enhancement_id' => 'enhancement1', 'level' => 3, 'cost_coin' => 300, 'enhancement_value' => 300],
            // enhancement2
            ['mst_outpost_enhancement_id' => 'enhancement2', 'level' => 1, 'cost_coin' => 0, 'enhancement_value' => 100],
            ['mst_outpost_enhancement_id' => 'enhancement2', 'level' => 2, 'cost_coin' => 200, 'enhancement_value' => 200],
        ]);

        /**
         * 探索系
         */
        MstIdleIncentive::factory()->create([
            'initial_reward_receive_minutes' => 10,
            'reward_increase_interval_minutes' => 10,
            'quick_idle_minutes' => 30,
            'required_quick_receive_diamond_amount' => 15,
            'max_daily_diamond_quick_receive_amount' => 3,
        ]);
        MstIdleIncentiveReward::factory()->create([
            'mst_stage_id' => 'stage1',
            'base_coin_amount' => 10,
            'base_exp_amount' => 20,
            'mst_idle_incentive_item_group_id' => 'itemGroup1',
        ]);
        MstIdleIncentiveItem::factory()->createMany([
            ['mst_idle_incentive_item_group_id' => 'itemGroup1', 'mst_item_id' => 'rewardItem1', 'base_amount' => 30],
        ]);

        /**
         * 降臨バトル系
         */
        MstAdventBattle::factory()->createMany([
            [
                'id' => 'adventbattle1',
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'id' => 'adventbattle2',
                'advent_battle_type' => AdventBattleType::RAID->value,
                'start_at' => '2024-03-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);
    }

    public function test_userService_consumeCoin_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();
        $this->createMstData();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);

        // Exercise 合計20消費
        app(UserService::class)->consumeCoin($usrUserId, 12, $now, new TestLogTrigger());
        app(UserService::class)->consumeCoin($usrUserId, 8, $now, new TestLogTrigger());

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::COIN_USED_COUNT, null, 20);
    }

    public static function params_test_idleIncentiveReceiveUseCase_exec_トリガーと進捗集約値の確認()
    {
        return [
            'normal' => [IdleIncentiveReceiveUseCase::class],
            'byAd' => [IdleIncentiveQuickReceiveByAdUseCase::class],
            'byDiamond' => [IdleIncentiveQuickReceiveByDiamondUseCase::class],
        ];
    }

    #[DataProvider('params_test_idleIncentiveReceiveUseCase_exec_トリガーと進捗集約値の確認')]
    public function test_idleIncentiveReceiveUseCase_exec_トリガーと進捗集約値の確認(string $useCaseClass)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $now = $this->fixTime();
        $this->createMstData();

        UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUserId,
            'idle_started_at' => $now->subMinutes(30),
            'reward_mst_stage_id' => 'stage1',
        ]);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'clear_count' => 1,
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'confumeItemId1',
            'amount' => 100,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId, 100);

        // Exercise
        app($useCaseClass)->exec($currentUser, UserConstant::PLATFORM_IOS, 'AppStore');

        // Verify
        switch ($useCaseClass) {
            case IdleIncentiveReceiveUseCase::class:
                $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IDLE_INCENTIVE_COUNT, null, 1);
                $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IDLE_INCENTIVE_QUICK_COUNT, null, 0, false);
                break;
            case IdleIncentiveQuickReceiveByAdUseCase::class:
                $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IDLE_INCENTIVE_COUNT, null, 0, false);
                $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IDLE_INCENTIVE_QUICK_COUNT, null, 1);
                $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IAA_COUNT, null, 1);
                break;
            case IdleIncentiveQuickReceiveByDiamondUseCase::class:
                $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IDLE_INCENTIVE_COUNT, null, 0, false);
                $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IDLE_INCENTIVE_QUICK_COUNT, null, 1);
                break;
        }
    }

    public function test_userBuyStaminaAdUseCase_exec_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $now = $this->fixTime();
        $this->createMstData();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 0,
        ]);
        $this->createDiamond($usrUserId, 100);

        // Exercise
        app(UserBuyStaminaAdUseCase::class)->exec($currentUser);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IAA_COUNT, null, 1);
    }

    public static function params_test_shopTradeShopItemUseCase_exec_トリガーと進捗集約値の確認()
    {
        return [
            'トリガーなし 初回無料アイテムの購入が1回目' => [
                'usrTradeCount' => 0,
                'isTrigger' => false,
            ],
            'トリガーあり 初回無料アイテムの購入が2回目' => [
                'usrTradeCount' => 1,
                'isTrigger' => true,
            ],
        ];
    }

    #[DataProvider('params_test_shopTradeShopItemUseCase_exec_トリガーと進捗集約値の確認')]
    public function test_shopTradeShopItemUseCase_exec_トリガーと進捗集約値の確認(
        int $usrTradeCount,
        bool $isTrigger
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $now = $this->fixTime();
        $this->createMstData();

        UsrShopItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_shop_item_id' => 'shopItem2',
            'trade_count' => $usrTradeCount,
            'last_reset_at' => $now->toDateTimeString(),
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 0,
        ]);
        $this->createDiamond($usrUserId, 100);

        // Exercise
        app(ShopTradeShopItemUseCase::class)->exec($currentUser, 'shopItem2', UserConstant::PLATFORM_IOS, 'AppStore');

        // Verify
        if ($isTrigger) {
            $this->checkTriggerAndAggregatedProgress(MissionCriterionType::IAA_COUNT, null, 1);
        } else {
            $this->checkExistMissionManagerTriggers(false);
        }
    }

    public function test_gameService_update_2日目の初回ログイン_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUser = $this->createUsrUser([
            'tutorial_status' => TutorialFunctionName::MAIN_PART_COMPLETED->value,
        ]);
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime('2024-10-22 00:00:00');
        $this->createMstData();

        // 7日ぶりにログインして、次ログイン時に、2日連続ログインになる
        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'last_login_at' => '2024-10-21 00:00:00', // 現在日時と最終ログイン日時が同じ1週間に入るように設定
            'login_day_count' => 1,
            'login_continue_day_count' => 1,
            'comeback_day_count' => 7,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId, 0);

        // Exercise
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());
        app(GameService::class)->update($usrUserId, UserConstant::PLATFORM_IOS, $now, Language::Ja->value, $gameStartAt);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::LOGIN_COUNT, null, 1);
        $this->checkTriggerAndAggregatedProgress(
            MissionCriterionType::LOGIN_CONTINUE_COUNT, null, 2, true,
            [MissionType::ACHIEVEMENT->value, MissionType::BEGINNER->value, MissionType::WEEKLY->value],
        );
    }

    public function test_usrItemService_addItems_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();
        $this->createMstData();

        // Exercise
        $itemMap = collect([
            'item1' => 1,
            'item2' => 2,
            'item3' => 3,
        ]);
        $rewards = $itemMap->map(function ($amount, $mstItemId) {
            return new BaseReward(
                RewardType::ITEM->value,
                $mstItemId,
                $amount,
                new LogTriggerDto('test', 'test', 'test')
            );
        });
        app(UsrItemService::class)->addItemByRewards($usrUserId, $rewards, $now);

        $itemMap = collect([
            'item1' => 11,
            'item2' => 20,
            'item3' => 29,
        ]);
        $rewards = $itemMap->map(function ($amount, $mstItemId) {
            return new BaseReward(
                RewardType::ITEM->value,
                $mstItemId,
                $amount,
                new LogTriggerDto('test', 'test', 'test')
            );
        });
        app(UsrItemService::class)->addItemByRewards($usrUserId, $rewards, $now);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ITEM_COLLECT, 'item1', 12);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ITEM_COLLECT, 'item2', 22);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ITEM_COLLECT, 'item3', 32);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ITEM_COLLECT, 'item4', 0, false); // 関係ないアイテムのトリガーは発火していない
    }

    public function test_outpostEnhanceUseCase_exec_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $this->createMstData();

        UsrOutpostEnhancement::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_enhancement_id' => 'enhancement1',
            'level' => 1,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId, 0);

        // Exercise
        app(OutpostEnhanceUseCase::class)->exec($currentUser, 'enhancement1', 3);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_OUTPOST_ENHANCE_LEVEL, 'enhancement1', 3);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_OUTPOST_ENHANCE_LEVEL, 'enhancement2', 0, false); // 関係ないゲートのトリガーは発火していない
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::OUTPOST_ENHANCE_COUNT, null, 2);
    }

    public function test_unitGradeUpService_gradeUp_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();
        $this->createMstData();

        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'level' => 1, 'grade_level' => 0,],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2', 'level' => 1, 'grade_level' => 0,],
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'item1',
            'amount' => 100,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId, 0);

        // Exercise
        app(UnitGradeUpService::class)->gradeUp('usrUnit1', $usrUserId);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'unit1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'unit2', 0, false); // 関係ないユニットのトリガーは発火していない
    }

    public function test_unitLevelUpService_levelUp_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();
        $this->createMstData();

        UsrUnit::factory()->create([
            'id' => 'usrUnit1',
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'unit1',
            'level' => 1,
            'rank' => 2,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId, 0);

        // Exercise
        app(UnitLevelUpService::class)->levelUp($usrUserId, 'usrUnit1', 3, $now);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'unit1', 3);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::UNIT_LEVEL, null, 3);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::UNIT_LEVEL_UP_COUNT, null, 2);
    }

    public function test_unitRankUpService_rankUp_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();
        $this->createMstData();

        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'level' => 1, 'rank' => 0,],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2', 'level' => 1, 'rank' => 0,],
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'rankUpMaterial',
            'amount' => 100,
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);
        $this->createDiamond($usrUserId, 0);

        // Exercise
        app(UnitRankUpService::class)->rankUp('usrUnit1', $usrUserId, $now);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'unit1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'unit2', 0, false); // 関係ないユニットのトリガーは発火していない
    }


    public static function params_test_stageEndUseCase_exec_トリガーと進捗集約値の確認()
    {
        return [
            'スタミナブーストなし' => [1],
            'スタミナブースト5周' => [5],
            'スタミナブースト10周' => [10],
        ];
    }

    #[DataProvider('params_test_stageEndUseCase_exec_トリガーと進捗集約値の確認')]
    public function test_stageEndUseCase_exec_トリガーと進捗集約値の確認(
        int $autoLapCount
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);

        $this->createMstData();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId, 3);

        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'party_no' => 2,
            'is_valid' => 1,
            'auto_lap_count' => $autoLapCount,
        ]);
        UsrStage::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage1',
            'clear_count' => 0,

        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'level' => 1,],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2', 'level' => 1,],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
        ]);
        UsrArtwork::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_artwork_id' => 'artwork4',],
        ]);
        UsrEnemyDiscovery::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_enemy_character_id' => 'enemy2-2',],
        ]);

        // Exercise
        app(StageEndUseCase::class)->exec($currentUser, UserConstant::PLATFORM_IOS, 'stage1', [
            'defeatEnemyCount' => 50,
            'defeatBossEnemyCount' => 2,
            'discoveredEnemies' => [
                // series1
                ['mstEnemyCharacterId' => 'enemy1-1', 'count' => 20],
                ['mstEnemyCharacterId' => 'enemy1-2', 'count' => 8],
                ['mstEnemyCharacterId' => 'bossEnemy1', 'count' => 1],
                // series2
                ['mstEnemyCharacterId' => 'enemy2-1', 'count' => 10],
                ['mstEnemyCharacterId' => 'enemy2-2', 'count' => 6], // 既知のエネミーで新発見カウントされない
                ['mstEnemyCharacterId' => 'enemy2-3', 'count' => 3],
                ['mstEnemyCharacterId' => 'enemy2-4', 'count' => 1],
                ['mstEnemyCharacterId' => 'bossEnemy2', 'count' => 1],
                // invalid
                ['mstEnemyCharacterId' => 'invalidEnemy', 'count' => 999], // テスト用の無効なデータ
            ],
        ]);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::STAGE_CLEAR_COUNT, null, $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage1', $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT, 'stage2', 0, false); // 関係ないステージのトリガーは発火していない
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_QUEST_CLEAR, 'quest1', 0, false); // 同一クエスト内のステージを全クリアしていないので、トリガー発火しない
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::QUEST_CLEAR_COUNT, null, 0, false); // 同一クエスト内のステージを全クリアしていないので、トリガー発火しない
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::DEFEAT_ENEMY_COUNT, null, 50 * $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::DEFEAT_BOSS_ENEMY_COUNT, null, 2 * $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT, 'unit1.stage1', $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT, 'unit2.stage1', $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT, 'unit1.stage2', 0, false); // 関係ないステージのトリガーは発火していない
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT, 'unit2.stage2', 0, false); // 関係ないステージのトリガーは発火していない
        // 原画完成系トリガー
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT, 'series1', 2);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT, 'series2', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT, 'series3', 0, false); // 過去に完成済み
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::ARTWORK_COMPLETED_COUNT, NULL, 3);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT, 'artwork1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT, 'artwork2', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT, 'artwork3', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT, 'artwork4', 0, false); // 過去に完成済み
        // インゲーム中に発見した敵のトリガー
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT, 'series1', 3);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT, 'series2', 4);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::ENEMY_DISCOVERY_COUNT, null, 7);

        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'enemy1-1', 20 * $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'enemy1-2', 8 * $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'bossEnemy1', $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'enemy2-1', 10 * $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'enemy2-2', 6 * $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'enemy2-3', 3 * $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'enemy2-4', $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'bossEnemy2', $autoLapCount);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT, 'invalidEnemy', 0, false);
    }

    public function test_stageEndUseCase_exec_同一クエスト内の全ステージクリアしたときのトリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);

        $this->createMstData();

        // usr
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId, 3);

        UsrStageSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_stage_id' => 'stage2',
            'is_valid' => 1,
        ]);
        UsrStage::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'stage1', 'clear_count' => 1, ], // クリア済み
            ['usr_user_id' => $usrUserId, 'mst_stage_id' => 'stage2', 'clear_count' => 0, ], // 未クリア
        ]);

        // Exercise
        app(StageEndUseCase::class)->exec($currentUser, UserConstant::PLATFORM_IOS, 'stage2'); // quest1の最後のステージをクリア

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_QUEST_CLEAR, 'quest1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::QUEST_CLEAR_COUNT, null, 1);
    }

    public function test_stageStartUseCase_exec_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);

        $this->createMstData();

        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'level' => 1,],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2', 'level' => 1,],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId, 3);

        // Exercise
        app(StageStartUseCase::class)->exec($currentUser, 'stage1', 2, false, 1);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_STAGE_CHALLENGE_COUNT, 'stage1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_STAGE_CHALLENGE_COUNT, 'stage2', 0, false); // 関係ないステージのトリガーは発火していない
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT, 'unit1.stage1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT, 'unit2.stage1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT, 'unit1.stage2', 0, false); // 関係ないステージのトリガーは発火していない
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT, 'unit2.stage2', 0, false); // 関係ないステージのトリガーは発火していない
    }

    public function test_userService_addExp_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();
        $this->createMstData();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
        ]);

        // Exercise
        app(UserService::class)->addExp($usrUserId, 200, $now);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::USER_LEVEL, null, 3);
    }

    public function test_rewardSendService_sendRewards_トリガーと進捗集約値の確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();
        $this->createMstData();

        // usr
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId, 'coin' => 1,]);
        UsrEmblem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_emblem_id' => 'emblem3'],
        ]);
        UsrArtwork::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_artwork_id' => 'artwork3'],
        ]);

        app(RewardManager::class)->addRewards(collect([
            // ユニット
            //   初獲得
            new Test1Reward(RewardType::UNIT, 'unit1', 1),
            //   初獲得 + 重複あり
            new Test1Reward(RewardType::UNIT, 'unit2', 2),
            new Test1Reward(RewardType::UNIT, 'unit3', 3),
            new Test1Reward(RewardType::UNIT, 'unit4', 1),
            new Test1Reward(RewardType::UNIT, 'unit4', 1),
            new Test1Reward(RewardType::UNIT, 'unit4', 1),
            new Test1Reward(RewardType::UNIT, 'unit4', 1),
            // エンブレム
            //   初獲得
            new Test1Reward(RewardType::EMBLEM, 'emblem1', 1),
            new Test1Reward(RewardType::EMBLEM, 'emblem4', 1),
            //   初獲得 + 重複あり
            new Test1Reward(RewardType::EMBLEM, 'emblem2', 2),
            //   重複
            new Test1Reward(RewardType::EMBLEM, 'emblem3', 3),
            new Test1Reward(RewardType::EMBLEM, 'emblem4', 1),
            new Test1Reward(RewardType::EMBLEM, 'emblem4', 1),
            new Test1Reward(RewardType::EMBLEM, 'emblem4', 1),
            // 原画（直接配布＝原画完成と同義）
            //   初獲得
            new Test1Reward(RewardType::ARTWORK, 'artwork1', 1),
            new Test1Reward(RewardType::ARTWORK, 'artwork4', 1),
            //   初獲得 + 重複あり
            new Test1Reward(RewardType::ARTWORK, 'artwork2', 2),
            //   重複
            new Test1Reward(RewardType::ARTWORK, 'artwork3', 3),
            new Test1Reward(RewardType::ARTWORK, 'artwork4', 1),
            new Test1Reward(RewardType::ARTWORK, 'artwork4', 1),
            new Test1Reward(RewardType::ARTWORK, 'artwork4', 1),
        ]));

        // Exercise
        app(RewardSendService::class)->sendRewards($usrUserId, UserConstant::PLATFORM_IOS, $now);

        // Verify
        // ユニット獲得系
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::UNIT_LEVEL, null, 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'unit1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'unit2', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'unit3', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_LEVEL, 'unit4', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::UNIT_ACQUIRED_COUNT, null, 4); // 初獲得のみカウント
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_UNIT_ACQUIRED_COUNT, 'series1', 2);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_UNIT_ACQUIRED_COUNT, 'series2', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_UNIT_ACQUIRED_COUNT, 'series3', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_ACQUIRED_COUNT, 'unit1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_ACQUIRED_COUNT, 'unit2', 2);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_ACQUIRED_COUNT, 'unit3', 3);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_ACQUIRED_COUNT, 'unit4', 4);
        // ユニット ステータス系
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'unit1', 0);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'unit2', 0);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'unit3', 0);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT, 'unit4', 0);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'unit1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'unit2', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'unit3', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT, 'unit4', 1);
        // エンブレム獲得系
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::EMBLEM_ACQUIRED_COUNT, null, 3); // 初獲得のみカウント
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_EMBLEM_ACQUIRED_COUNT, 'series1', 2);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_EMBLEM_ACQUIRED_COUNT, 'series2', 0, false);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_EMBLEM_ACQUIRED_COUNT, 'series3', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_EMBLEM_ACQUIRED_COUNT, 'emblem1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_EMBLEM_ACQUIRED_COUNT, 'emblem2', 2);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_EMBLEM_ACQUIRED_COUNT, 'emblem3', 3);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_EMBLEM_ACQUIRED_COUNT, 'emblem4', 4);
        // エンブレム・原画重複獲得によるコイントリガー
        // エンブレム: emblem2(重複1) + emblem3(重複3) + emblem4(重複3) = 7
        // 原画: artwork2(重複1) + artwork3(重複3) + artwork4(重複3) = 7
        $emblemCoin = 7 * EmblemConstant::DUPLICATE_EMBLEM_CONVERT_COIN;
        $artworkCoin = 7 * EncyclopediaConstant::DUPLICATE_ARTWORK_CONVERT_COIN;
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::COIN_COLLECT, null, $emblemCoin + $artworkCoin);
        // 原画完成系（直接配布＝原画完成）
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::ARTWORK_COMPLETED_COUNT, null, 3); // 初獲得のみカウント
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT, 'series1', 2);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT, 'series2', 0, false); // artwork3は既所持→トリガーなし
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_SERIES_ARTWORK_COMPLETED_COUNT, 'series3', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT, 'artwork1', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT, 'artwork2', 1);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT, 'artwork3', 0, false); // 既所持→トリガーなし
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT, 'artwork4', 1);
    }

    public function test_gachaDrawUseCase_exec_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);

        $this->createMstData();

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId, 999);

        // Exercise
        app(GachaDrawUseCase::class)->exec($currentUser, 'gacha1', 0, 10, null, 10, UserConstant::PLATFORM_IOS, CurrencyConstants::PLATFORM_APPSTORE, CostType::DIAMOND);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::GACHA_DRAW_COUNT, null, 10);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_GACHA_DRAW_COUNT, 'gacha1', 10);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::SPECIFIC_GACHA_DRAW_COUNT, 'gacha2', 0, false); // 対象外のガシャのトリガーは発火していない
    }

    public function test_adventBattleStartUseCase_exec_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);

        $this->createMstData();

        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'level' => 1,],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2', 'level' => 1,],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId, 3);

        // Exercise
        app(AdventBattleStartUseCase::class)->exec($currentUser, 'adventbattle1', 2, false, []);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT, null, 1);
    }

    public function test_adventBattleEndUseCase_exec_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $currentUser = new CurrentUser($usrUserId);
        $now = $this->fixTime();

        $this->createMstData();

        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'level' => 1,],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2', 'level' => 1,],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'stamina' => 100,
        ]);
        $this->createDiamond($usrUserId, 3);

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => 'adventbattle1',
            'max_score' => 15000,
            'total_score' => 15000,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => 'adventbattle1',
            'is_valid' => AdventBattleSessionStatus::STARTED->value,
            'party_no' => 1,
            'battle_start_at' => $now->subSeconds(200)->toDateTimeString(),
        ]);

        // Exercise
        $inGameBattleLog = [
            'defeatEnemyCount' => 9,
            'defeatBossEnemyCount' => 1,
        ];
        app(AdventBattleEndUseCase::class)->exec($currentUser, 'adventbattle1', UserConstant::PLATFORM_IOS, $inGameBattleLog);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::DEFEAT_ENEMY_COUNT, null, 9);
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::DEFEAT_BOSS_ENEMY_COUNT, null, 1);
    }

    public function test_userAccountLinkService_linkBnid_トリガーと進捗集約値の確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();
        $this->createMstData();

        $platform = UserConstant::PLATFORM_ANDROID;
        $usrDevice = UsrDevice::factory()->create([
            'usr_user_id' => $usrUserId
        ]);
        $accessToken = app(AccessTokenService::class)->create($usrUserId, $usrDevice->getId(), $now);

        // Exercise
        $code = 'dummy';
        app(UserAccountLinkService::class)->linkBnid($usrUserId, $platform, $code, false, $accessToken, '127.0.0.1', $now);

        // Verify
        $this->checkTriggerAndAggregatedProgress(MissionCriterionType::ACCOUNT_COMPLETED, null, 1);
    }
}
