<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaReward;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Game\UseCases\GameBadgeUseCase;
use App\Domain\Game\UseCases\GameFetchUseCase;
use App\Domain\Game\UseCases\GameServerTimeUseCase;
use App\Domain\Game\UseCases\GameVersionUseCase;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\InGame\Models\UsrEnemyDiscovery;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Models\UsrItemTrade;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionDailyBonusType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Party\Models\Eloquent\UsrArtworkParty;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mng\Models\MngContentClose;
use App\Domain\Resource\Mng\Models\MngInGameNotice;
use App\Domain\Resource\Mng\Models\MngInGameNoticeI18n;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstComebackBonus;
use App\Domain\Resource\Mst\Models\MstComebackBonusSchedule;
use App\Domain\Resource\Mst\Models\MstDailyBonusReward;
use App\Domain\Resource\Mst\Models\MstMissionDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonus;
use App\Domain\Resource\Mst\Models\MstMissionEventDailyBonusSchedule;
use App\Domain\Resource\Mst\Models\MstMissionReward;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstShopItem;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprCampaign;
use App\Domain\Resource\Mst\Models\OprCampaignI18n;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\MstPackCostType;
use App\Domain\Shop\Enums\PackType;
use App\Domain\Shop\Enums\ShopItemCostType;
use App\Domain\Shop\Enums\ShopType;
use App\Domain\Shop\Models\UsrConditionPack;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Models\UsrStoreProduct;
use App\Domain\Shop\Models\UsrTradePack;
use App\Domain\Shop\Models\UsrWebstoreInfo;
use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStageEnhance;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Tutorial\Models\UsrTutorial;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserBuyCount;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use App\Exceptions\HttpStatusCode;
use App\Http\Responses\Data\GameBadgeData;
use App\Http\Responses\Data\GameFetchData;
use App\Http\Responses\Data\MissionStatusData;
use App\Http\Responses\Data\UsrParameterData;
use App\Http\Responses\ResultData\GameBadgeResultData;
use App\Http\Responses\ResultData\GameFetchResultData;
use App\Http\Responses\ResultData\GameServerTimeResultData;
use App\Http\Responses\ResultData\GameVersionResultData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Mockery\MockInterface;
use Tests\Support\Traits\TestMissionTrait;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;

class GameControllerTest extends BaseControllerTestCase
{
    use TestMissionTrait;

    protected string $baseUrl = '/api/game/';

    public function testVersion_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $usrUser = $this->createUsrUser();

        $masterDataManifest = [
            'hash' => '6a2de329245449e431f66c9806568d74',
            'path' => 'masterdata/masterdata_6a2de329245449e431f66c9806568d74.json',
            'i18nHash' => 'e1107d264aaf17a0ea8bc11f14792d07',
            'i18nPath' => 'operationdata/operationdata_e1107d264aaf17a0ea8bc11f14792d07.json',
        ];

        $operationDataManifest = [
            'hash' => 'e1107d264aaf17a0ea8bc11f14792d07',
            'path' => 'operationdata/operationdata_e1107d264aaf17a0ea8bc11f14792d07.json',
            'i18nHash' => 'e1107d264aaf17a0ea8bc11f14792d07',
            'i18nPath' => 'operationdata/operationdata_e1107d264aaf17a0ea8bc11f14792d07.json',
        ];

        $assetDataManifest = [
            'catalog_data_path' => 'assetbundles/ios/test_hash/catalog_1.data',
            'asset_hash' => 'test_hash',
            'asset_version' => '1.0.0',
        ];

        $resultData = new GameVersionResultData(
            $masterDataManifest['hash'],
            $masterDataManifest['path'],
            $masterDataManifest['i18nHash'],
            $masterDataManifest['i18nPath'],
            $operationDataManifest['hash'],
            $operationDataManifest['path'],
            $masterDataManifest['i18nHash'],
            $masterDataManifest['i18nPath'],
            $assetDataManifest['catalog_data_path'],
            $assetDataManifest['asset_hash'],
            2, // tosVersion
            1, // tosUserAgreeVersion
            'https://example.com/tos', // tosUrl
            3, // privacyPolicyVersion
            2, // privacyPolicyUserAgreeVersion
            'https://example.com/privacy', // privacyPolicyUrl
            1, // globalCnsntVersion
            0, // globalCnsntUserAgreeVersion
            'https://example.com/global-consent', // globalCnsntUrl
            1, // iaaVersion
            0, // iaaUserAgreeVersion
            'https://example.com/iaa' // iaaUrl
        );
        $this->mock(GameVersionUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $response = $this->withHeaders([
            System::HEADER_LANGUAGE => Language::Ja->value,
            System::HEADER_PLATFORM => 1,
            System::HEADER_ASSET_VERSION => "1.0.0",
        ])->sendGetRequest('version');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertEquals($resultData->mstHash, $response['mstHash']);
        $this->assertEquals($resultData->mstPath, $response['mstPath']);


        $this->assertEquals($resultData->mstI18nHash, $response['mstI18nHash']);
        $this->assertEquals($resultData->mstI18nPath, $response['mstI18nPath']);

        $this->assertEquals($resultData->oprHash, $response['oprHash']);
        $this->assertEquals($resultData->oprPath, $response['oprPath']);

        $this->assertEquals($resultData->oprI18nHash, $response['oprI18nHash']);
        $this->assertEquals($resultData->oprI18nPath, $response['oprI18nPath']);

        $this->assertEquals($resultData->assetCatalogDataPath, $response['assetCatalogDataPath']);
        $this->assertEquals($resultData->assetHash, $response['assetHash']);

        // 追加した利用規約関連のプロパティの検証
        $this->assertEquals($resultData->tosVersion, $response['tosVersion']);
        $this->assertEquals($resultData->tosUserAgreeVersion, $response['tosUserAgreeVersion']);
        $this->assertEquals($resultData->tosUrl, $response['tosUrl']);
        $this->assertEquals($resultData->privacyPolicyVersion, $response['privacyPolicyVersion']);
        $this->assertEquals($resultData->privacyPolicyUserAgreeVersion, $response['privacyPolicyUserAgreeVersion']);
        $this->assertEquals($resultData->privacyPolicyUrl, $response['privacyPolicyUrl']);
        $this->assertEquals($resultData->globalCnsntVersion, $response['globalCnsntVersion']);
        $this->assertEquals($resultData->globalCnsntUserAgreeVersion, $response['globalCnsntUserAgreeVersion']);
        $this->assertEquals($resultData->globalCnsntUrl, $response['globalCnsntUrl']);
        $this->assertEquals($resultData->iaaVersion, $response['inAppAdvertisementVersion']);
        $this->assertEquals($resultData->iaaUserAgreeVersion, $response['inAppAdvertisementUserAgreeVersion']);
        $this->assertEquals($resultData->iaaUrl, $response['inAppAdvertisementUrl']);
    }

    private function generateUser(CarbonImmutable $now): UsrUser
    {
        $usrUser = $this->createUsrUser();
        $usrUser->tutorial_status = TutorialFunctionName::MAIN_PART_COMPLETED->value;
        $usrUser->game_start_at = $now->toDateTimeString();

        $usrUser->save();

        return $usrUser;
    }

    private function generateUserParameter(UsrUser $usrUser): UsrUserParameter
    {
        $this->createDiamond($usrUser->getId(), 6, 7, 8);

        return UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 2,
            'exp' => 3,
            'coin' => 4,
            'stamina' => 5,
            'stamina_updated_at' => now()->sub('1 hour'),
        ]);
    }

    private function generateStageItemUnit(UsrUser $usrUser): array
    {
        $usrStages = collect();
        $usrStageEvents = collect();
        $usrStageEnhances = collect();
        $usrItems = collect();
        $usrUnits = collect();
        for ($i = 1; $i <= 3; $i++) {
            $usrStages->push(
                UsrStage::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_stage_id' => (string) $i,
                    'clear_count' => $i,
                    'clear_time_ms' => $i * 100,
                ])
            );
            $usrStageEvents->push(
                UsrStageEvent::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_stage_id' => (string) $i,
                    'clear_count' => $i,
                    'reset_clear_count' => $i,
                    'reset_ad_challenge_count' => $i,
                    'latest_reset_at' => now()->toDateTimeString(),
                    'last_challenged_at' => now()->toDateTimeString(),
                ])
            );
            $usrStageEnhances->push(
                UsrStageEnhance::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_stage_id' => (string) $i,
                    'clear_count' => $i,
                    'reset_challenge_count' => $i,
                    'reset_ad_challenge_count' => $i,
                    'max_score' => $i,
                    'latest_reset_at' => $i == 3
                        ? now()->subDay()->toDateTimeString() // i=3はリセットする
                        : now()->toDateTimeString() // i=1,2はリセットしない
                ])
            );
            $usrItems->push(
                UsrItem::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_item_id' => (string) $i,
                    'amount' => $i,
                ])
            );
            $usrUnits->push(
                UsrUnit::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_unit_id' => (string) $i,
                    'level' => $i,
                    'rank' => $i,
                    'grade_level' => $i,
                ])
            );
        }
        return [$usrStages, $usrStageEvents, $usrStageEnhances, $usrItems, $usrUnits];
    }

    private function generateShopData(UsrUser $usrUser): array
    {
        $oprProductId = fake()->uuid();
        $mstShopItemId = fake()->uuid();
        $oprProducts = collect();
        $oprProducts->push(
            OprProduct::factory()->create([
                'id' => $oprProductId,
                'mst_store_product_id' => fake()->uuid(),
                'product_type' => 'diamond',
                'purchasable_count' => 1,
                'paid_amount' => 100,
                'display_priority' => 1,
                'start_date' => '2000-01-01 00:00:00',
                'end_date' => '2030-01-01 00:00:00',
            ])->toEntity()
        );
        $mstShopItems = collect();
        $mstShopItems->push(
            MstShopItem::factory()->create([
                'id' => $mstShopItemId,
                'shop_type' => ShopType::COIN->value,
                'cost_type' => ShopItemCostType::DIAMOND->value,
                'cost_amount' => 10,
                'is_first_time_free' => 0,
                'tradable_count' => 1,
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
                'start_date' => '2000-01-01 00:00:00',
                'end_date' => '2030-01-01 00:00:00'
            ])->toEntity()
        );
        $usrStoreProducts = collect();
        $usrStoreProducts->push(
            UsrStoreProduct::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'product_sub_id' => $oprProductId,
                'purchase_count' => 1,
                'purchase_total_count' => 1,
                'last_reset_at' => now(),
            ])
        );
        $usrShopItems = collect();
        $usrShopItems->push(
            UsrShopItem::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'mst_shop_item_id' => $mstShopItemId,
                'trade_count' => 1,
                'trade_total_count' => 1,
                'last_reset_at' => now(),
            ])
        );

        $usrConditionPacks = collect();
        $usrConditionPacks->push(
            UsrConditionPack::factory()->create([
                'usr_user_id' => $usrUser->getId(),
                'mst_pack_id' => fake()->uuid(),
                'start_date' => now()->format('Y-m-d H:i:s'),
            ])
        );

        return [$oprProducts, $mstShopItems, $usrStoreProducts, $usrShopItems, $usrConditionPacks];
    }

    private function generateAdventBattle(UsrUser $usrUser): Collection
    {
        $usrAdventBattles = collect();
        for ($i = 1; $i <= 3; $i++) {
            MstAdventBattle::factory()->create([
                'id' => (string) $i,
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => now()->subDay()->toDateTimeString(),
                'end_at' => now()->addDay()->toDateTimeString(),
            ]);
            $usrAdventBattles->push(
                UsrAdventBattle::factory()->create([
                    'usr_user_id' => $usrUser->getId(),
                    'mst_advent_battle_id' => (string) $i,
                    'max_score' => $i,
                    'total_score' => $i * 2,
                    'reset_challenge_count' => $i,
                    'reset_ad_challenge_count' => $i,
                    'clear_count' => $i * 10, // Add clear_count for testing
                    'latest_reset_at' => $i == 3
                        ? now()->subDay()->toDateTimeString() // i=3はリセットする
                        : now()->toDateTimeString() // i=1,2はリセットしない
                ])
            );
        }
        return $usrAdventBattles;
    }

    public function testUpdateAndFetch_結合テスト()
    {
        // Setup
        $now = $this->fixTime('2025-06-13 12:00:00');
        $subDay = $now->copy()->subDay();

        $usrUser = $this->generateUser($now);
        $userId = $usrUser->getId();
        $usrUserParameter = $this->generateUserParameter($usrUser);
        $usrUserProfile = UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'my_id' => '9999999999',
            'name' => 'test',
            'is_change_name' => 1,
            'name_update_at' => now()->sub('2 hour'),
            'birth_date' => '',
        ]);

        $usrStoreInfo = UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'age' => 20,
            'paid_price' => 100,
            'renotify_at' => null,
        ]);

        [$usrStages, , , $usrItems, $usrUnits] = $this->generateStageItemUnit($usrUser);
        $usrParty = UsrParty::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'party_no' => 1,
            'party_name' => 'party 1',
            'usr_unit_id_1' => $usrUnits[0]->getId(),
            'usr_unit_id_2' => $usrUnits[1]->getId(),
            'usr_unit_id_3' => $usrUnits[2]->getId(),
        ]);
        $usrArtworkParty = UsrArtworkParty::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id_1' => 'artwork_tutorial_0001',
        ]);
        [
            $oprProducts,
            $mstShopItems,
            $usrStoreProducts,
            $usrShopItems,
            $usrConditionPacks
        ] = $this->generateShopData($usrUser);

        $notResetAt = $now->toDateTimeString();
        $usrItemTrades = UsrItemTrade::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_item_id' => 'tradedItem1', 'trade_amount' => 101, 'reset_trade_amount' => 1, 'trade_amount_reset_at' => $notResetAt],
            ['usr_user_id' => $usrUser->getId(), 'mst_item_id' => 'tradedItem2', 'trade_amount' => 102, 'reset_trade_amount' => 2, 'trade_amount_reset_at' => $notResetAt],
            ['usr_user_id' => $usrUser->getId(), 'mst_item_id' => 'tradedItem3', 'trade_amount' => 103, 'reset_trade_amount' => 3, 'trade_amount_reset_at' => $notResetAt],
        ]);

        $subDayDateTimeString = $now->copy()->subDays(1)->toDateTimeString();
        $usrIdleIncentive = UsrIdleIncentive::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'diamond_quick_receive_count' => 1,
            'ad_quick_receive_count' => 2,
            'idle_started_at' => $subDayDateTimeString,
            'diamond_quick_receive_at' => $subDayDateTimeString,
            'ad_quick_receive_at' => $subDayDateTimeString,
        ]);

        $usrOutposts = UsrOutpost::factory()->count(2)->sequence(
            ['usr_user_id' => $usrUser->getId(), 'mst_outpost_id' => '1', 'is_used' => 0],
            ['usr_user_id' => $usrUser->getId(), 'mst_outpost_id' => '2', 'is_used' => 1],
        )->create();

        $usrOutpostEnhancements = UsrOutpostEnhancement::factory()->count(3)->sequence(
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_outpost_id' => '1',
                'mst_outpost_enhancement_id' => '1-1',
                'level' => 1,
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_outpost_id' => '1',
                'mst_outpost_enhancement_id' => '1-2',
                'level' => 2,
            ],
            [
                'usr_user_id' => $usrUser->getId(),
                'mst_outpost_id' => '2',
                'mst_outpost_enhancement_id' => '2-1',
                'level' => 3,
            ],
        )->create();

        /**
         * mission
         * ミッション報酬未受け取りのバッジ取得のためのデータ用意
         * ミッションマスタデータのCOIN_COLLECT指定は特に意味がなく、マスタデータを用意するためだけのものです
         */
        // achievement
        $this->createMstMission(MissionType::ACHIEVEMENT, 'achievement1', MissionCriterionType::COIN_COLLECT, null, 10);
        $this->createUsrMissionNormal($userId, MissionType::ACHIEVEMENT, 'achievement1', MissionStatus::CLEAR, 999, $subDay, null, $now);
        // daily
        $this->createMstMission(MissionType::DAILY, 'daily2', MissionCriterionType::COIN_COLLECT, null, 10);
        $this->createUsrMissionNormal($userId, MissionType::DAILY, 'daily2', MissionStatus::CLEAR, 999, $subDay, null, $now);
        // weekly
        $this->createMstMission(MissionType::WEEKLY, 'weekly1', MissionCriterionType::COIN_COLLECT, null, 10);
        $this->createUsrMissionNormal($userId, MissionType::WEEKLY, 'weekly1', MissionStatus::CLEAR, 999, $subDay, null, $now);
        // beginner
        $this->createMstMission(MissionType::BEGINNER, 'beginner1', MissionCriterionType::COIN_COLLECT, null, 10);
        $this->createUsrMissionNormal($userId, MissionType::BEGINNER, 'beginner1', MissionStatus::CLEAR, 999, $subDay, null, $now);


        MstMissionDailyBonus::factory()->createMany([
            [
                'id' => 'dailyBonus-1',
                'mission_daily_bonus_type' => MissionDailyBonusType::DAILY_BONUS->value,
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => 'missionRewardGroup2',
            ],
        ]);
        MstMissionEventDailyBonus::factory()->createMany([
            [
                'id' => 'bonus_1',
                'mst_mission_event_daily_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 1,
                'mst_mission_reward_group_id' => 'missionRewardGroup1',
            ],
        ]);
        MstMissionEventDailyBonusSchedule::factory()->createMany([
            [
                'id' => 'schedule_1',
                'mst_event_id' => 'event_1',
            ],
        ]);
        MstComebackBonus::factory()->createMany([
            [
                'id' => 'bonus_1',
                'mst_comeback_bonus_schedule_id' => 'schedule_1',
                'login_day_count' => 1,
                'mst_daily_bonus_reward_group_id' => 'dailyBonusReward1',
            ],
        ]);
        MstComebackBonusSchedule::factory()->createMany([
            [
                'id' => 'schedule_1',
                'inactive_condition_days' => 1,
            ],
        ]);
        MstMissionReward::factory()->createMany([
            [
                'group_id' => 'missionRewardGroup1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 100,
            ],
            [
                'group_id' => 'missionRewardGroup2',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 200,
            ],
        ]);
        MstDailyBonusReward::factory()->createMany([
            [
                'group_id' => 'dailyBonusReward1',
                'resource_type' => RewardType::COIN->value,
                'resource_id' => null,
                'resource_amount' => 300,
            ],
        ]);

        UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'daily_buy_stamina_ad_count' => 1,
            // リセットが行われるよう1日前の日付とする
            'daily_buy_stamina_ad_at' => $now->subDay()->toDateTimeString(),
        ]);

        // ingame
        UsrEnemyDiscovery::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_enemy_character_id' => 'enemy1'],
        ]);

        // encyclopedia
        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => '1',
        ]);
        UsrArtworkFragment::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_artwork_id' => '1',
            'mst_artwork_fragment_id' => '1',
        ]);
        $receivedMstUnitEncyclopediaRewardIds = UsrReceivedUnitEncyclopediaReward::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_encyclopedia_reward_id' => '1'],
            ['usr_user_id' => $usrUser->getId(), 'mst_unit_encyclopedia_reward_id' => '2'],
        ])->map(fn($model) => $model->getMstUnitEncyclopediaRewardId());

        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);

        // IGN
        MngInGameNotice::factory()->createMany([
            ['id' => 'ign1', 'enable' => 1], // 有効
            ['id' => 'ign2', 'enable' => 1], // 有効
            ['id' => 'ign3', 'enable' => 0], // 無効フラグで無効
            ['id' => 'ign4', 'enable' => 1, 'start_at' => '2000-01-01 00:00:00', 'end_at' => '2001-01-01 00:00:00'], // 期間外で無効
        ]);
        MngInGameNoticeI18n::factory()->createMany([
            ['mng_in_game_notice_id' => 'ign1', 'language' => Language::Ja->value, 'title' => 'IGN1-ja'],
            ['mng_in_game_notice_id' => 'ign2', 'language' => Language::Ja->value, 'title' => 'IGN2-ja'],
            ['mng_in_game_notice_id' => 'ign3', 'language' => Language::Ja->value, 'title' => 'IGN3-ja'],
            ['mng_in_game_notice_id' => 'ign4', 'language' => Language::Ja->value, 'title' => 'IGN4-ja'],
        ]);

        // Content Close
        MngContentClose::factory()->createMany([
            [
                'id' => 'content_close_1',
                'content_type' => 'Gacha',
                'start_at' => $now->subHours(2)->toDateTimeString(),
                'end_at' => $now->addHours(2)->toDateTimeString(),
                'is_valid' => 1,
            ],
            [
                'id' => 'content_close_2',
                'content_type' => 'Pvp',
                'start_at' => $now->addHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(3)->toDateTimeString(),
                'is_valid' => 1,
            ],
            [
                'id' => 'content_close_3',
                'content_type' => 'Shop',
                'start_at' => $now->subDays(1)->toDateTimeString(),
                'end_at' => $now->subHours(1)->toDateTimeString(),
                'is_valid' => 1,
            ],
            [
                'id' => 'content_close_4',
                'content_type' => 'AdventBattle',
                'start_at' => $now->subHours(1)->toDateTimeString(),
                'end_at' => $now->addHours(1)->toDateTimeString(),
                'is_valid' => 0, // 無効フラグ
            ],
        ]);

        // Campaign
        OprCampaign::factory()->createMany([
            [
                'id' => 'campaign1',
            ],
            [
                'id' => 'campaign2',
                'start_at' => '2000-01-01 00:00:00',
                'end_at' => '2001-01-01 00:00:00',
            ]
        ]);
        OprCampaignI18n::factory()->createMany([
            ['opr_campaign_id' => 'campaign1', 'language' => Language::Ja->value],
            ['opr_campaign_id' => 'campaign2', 'language' => Language::Ja->value],
        ]);

        // AdventBattle
        $mstAdventBattleId = MstAdventBattle::factory()->create([
            'advent_battle_type' => AdventBattleType::RAID->value,
            'start_at' => now()->subDay()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ])->toEntity()->getId();
        $key = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);
        Redis::connection()->zadd($key, [$userId => 1]);
        $usrAdventBattles = $this->generateAdventBattle($usrUser);

        // PVP
        $mstPvp = MstPvp::factory()->create([
            'id' => 'default_pvp',
            'max_daily_challenge_count' => 3,
            'max_daily_item_challenge_count' => 2,
        ]);
        $sysPvpSeason = SysPvpSeason::factory()->create([
            'id' => '2025024',
        ]);
        $usrPvp = UsrPvp::factory()->create([
            'usr_user_id' => $userId,
            'sys_pvp_season_id' => $sysPvpSeason->id,
            'score' => 1500,
            'pvp_rank_class_type' => PvpRankClassType::SILVER->value,
            'pvp_rank_class_level' => 3,
            'daily_remaining_challenge_count' => 2,
            'daily_remaining_item_challenge_count' => 1,
            'latest_reset_at' => $now->subDay()->toDateTimeString(),
        ]);

        // Tutorial
        MstTutorial::factory()->createMany([
            // 有効
            ['id' => 'id_freeTutorial1', 'type' => TutorialType::FREE, 'function_name' => 'freeTutorial1', 'start_at' => '2020-01-01 00:00:00', 'end_at' => '2037-01-01 00:00:00'],
            ['id' => 'id_freeTutorial2', 'type' => TutorialType::FREE, 'function_name' => 'freeTutorial2', 'start_at' => '2020-01-01 00:00:00', 'end_at' => '2037-01-01 00:00:00'],
            // 無効
            ['id' => 'id_freeTutorial3', 'type' => TutorialType::FREE, 'function_name' => 'freeTutorial3', 'start_at' => '2020-01-01 00:00:00', 'end_at' => '2021-01-01 00:00:00'],
        ]);
        UsrTutorial::factory()->createMany([
            ['usr_user_id' => $userId, 'mst_tutorial_id' => 'id_freeTutorial1'],
            ['usr_user_id' => $userId, 'mst_tutorial_id' => 'id_freeTutorial2'],
            // レコードがあるが期間的に無効なのでレスポンスされない
            ['usr_user_id' => $userId, 'mst_tutorial_id' => 'id_freeTutorial3'],
        ]);
        // UsrDevice and AccessToken
        $deviceId = 'device1';
        $bnidLinkedAt = now()->toDateTimeString();
        UsrDevice::factory()->create([
            'id' => $deviceId,
            'usr_user_id' => $usrUser->getId(),
            'bnid_linked_at' => $bnidLinkedAt,
        ]);
        $accessToken = 'access-token';
        $this->setToRedis("token:userid:deviceid:$accessToken", "$userId,$deviceId");
        $this->setToRedis("userid:token:$userId", $accessToken);

        UsrUserLogin::factory()->create([
            'usr_user_id' => $userId,
            'last_login_at' => $now->subDays(2)->toDateTimeString(),
            'login_count' => 0,
            'login_day_count' => 0,
            'login_continue_day_count' => 0,
            'comeback_day_count' => 0,
        ]);

        // Gacha
        $gachaId = 'gacha1';
        OprGacha::factory()->create([
            'id' => $gachaId,
            'start_at' => now()->subDay()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ]);
        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'opr_gacha_id' => $gachaId,
            'expires_at' => now()->addDay()->toDateTimeString(),
        ]);

        $mstPack = MstPack::factory()->create([
            'product_sub_id' => fake()->uuid(),
            'pack_type'=> PackType::DAILY->value,
            'sale_condition' => null,
            'cost_type' => MstPackCostType::DIAMOND->value,
            'tradable_count' => 5,
            'cost_amount' => 10,
        ])->toEntity();

        UsrTradePack::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_pack_id' => $mstPack->getId(),
            'daily_trade_count' => 3,
            'last_reset_at' => now()->subDay(3)->toDateTimeString(),
        ]);

        // WebStore購入通知をRedisにセット
        $productSubId1 = 'product_sub_id_1';
        $productSubId2 = 'product_sub_id_2';
        $cacheKey = CacheKeyUtil::getWebStorePurchaseNotificationsKey($userId);
        $this->setToRedis($cacheKey, [$productSubId1, $productSubId2]);

        // Exercise
        $response = $this
            ->withHeaders(['Language' => Language::Ja->value, 'Access-Token' => $accessToken])
            ->sendRequest('update_and_fetch', ['countryCode' => 'JP']);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンス確認

        /**
         * fetch
         */

        $this->assertArrayHasKey('fetch', $response);
        $responseFetch = $response['fetch'];

        // usrParameter
        $this->assertArrayHasKey('usrParameter', $responseFetch);
        $this->assertEquals($usrUserParameter->getLevel(), $responseFetch['usrParameter']['level']);
        $this->assertEquals($usrUserParameter->getExp(), $responseFetch['usrParameter']['exp']);
        $this->assertEquals(4 + 100 + 200 + 300, $responseFetch['usrParameter']['coin']);
        $this->assertEquals(10, $responseFetch['usrParameter']['stamina']);
        $this->assertEquals($now->toAtomString(), $responseFetch['usrParameter']['staminaUpdatedAt']);
        $this->assertEquals(6, $responseFetch['usrParameter']['freeDiamond']);
        $this->assertEquals(7, $responseFetch['usrParameter']['paidDiamondIos']);
        $this->assertEquals(8, $responseFetch['usrParameter']['paidDiamondAndroid']);

        // usrStages
        $this->assertArrayHasKey('usrStages', $responseFetch);
        $this->assertCount(3, $responseFetch['usrStages']);
        $expected = $usrStages->first();
        $actual = collect($responseFetch['usrStages'])->keyBy('mstStageId')->get($expected->getMstStageId());
        $this->assertEquals($expected->getMstStageId(), $actual['mstStageId']);
        $this->assertEquals($expected->getClearTimeMs(), $actual['clearTimeMs']);
        $this->assertEquals($expected->getClearCount(), $actual['clearCount']);

        // usrStageEnhances
        $this->assertArrayHasKey('usrStageEnhances', $responseFetch);
        $actuals = collect($responseFetch['usrStageEnhances'])->keyBy('mstStageId');
        $this->assertCount(3, $actuals);
        // リセットされないデータ
        $actual = $actuals->get('1');
        $this->assertEquals(1, $actual['resetChallengeCount']);
        $this->assertEquals(1, $actual['resetAdChallengeCount']);
        $this->assertEquals(1, $actual['maxScore']);
        // リセットされるデータ
        $actual = $actuals->get('3');
        $this->assertEquals(0, $actual['resetChallengeCount']);
        $this->assertEquals(0, $actual['resetAdChallengeCount']);
        $this->assertEquals(3, $actual['maxScore']);

        // mission
        $this->assertArrayHasKey('badges', $responseFetch);
        $badges = $responseFetch['badges'];
        $this->assertArrayHasKey('unreceivedMissionRewardCount', $badges);
        $this->assertEquals(3, $badges['unreceivedMissionRewardCount']);
        $this->assertArrayHasKey('unreceivedMissionBeginnerRewardCount', $badges);
        $this->assertEquals(1, $badges['unreceivedMissionBeginnerRewardCount']);

        // usrBuyCount
        $this->assertArrayHasKey('usrBuyCount', $responseFetch);
        $usrBuyCount = $responseFetch['usrBuyCount'];
        $this->assertEquals(0, $usrBuyCount['dailyBuyStaminaAdCount']);
        $this->assertEquals($now->subDay()->toAtomString(), $usrBuyCount['dailyBuyStaminaAdAt']);

        // usrAdventBattles
        $this->assertArrayHasKey('usrAdventBattles', $responseFetch);
        $this->assertCount(3, $responseFetch['usrAdventBattles']);
        $adventBattleResponses = collect($responseFetch['usrAdventBattles'])->keyBy('mstAdventBattleId');
        $adventBattleResponses->each(function ($adventBattle, $id) {
            $this->assertArrayHasKey('clearCount', $adventBattle);
            $expectedClearCount = ((int)$id) * 10; // Based on our test data
            $this->assertEquals($expectedClearCount, $adventBattle['clearCount']);
        });

        /**
         * fetchOther
         */

        $this->assertArrayHasKey('fetchOther', $response);
        $responseFetchOther = $response['fetchOther'];

        // user
        $this->assertEquals($usrUser->getTutorialStatus(), $responseFetchOther['tutorialStatus']);

        $this->assertArrayHasKey('usrProfile', $responseFetchOther);
        $this->assertEquals($usrUserProfile->getName(), $responseFetchOther['usrProfile']['name']);
        $this->assertEquals($usrUserProfile->getMyId(), $responseFetchOther['usrProfile']['myId']);
        $this->assertEquals(StringUtil::convertToISO8601($usrUserProfile->getNameUpdateAt()), $responseFetchOther['usrProfile']['nameUpdateAt']);

        $this->assertArrayHasKey('usrLogin', $responseFetchOther);
        $actual = $responseFetchOther['usrLogin'];
        $this->assertEquals(StringUtil::convertToISO8601($now->toDateTimeString()), $actual['lastLoginAt']);
        $this->assertEquals(1, $actual['loginDayCount']);
        $this->assertEquals(1, $actual['loginContinueDayCount']);

        // tutorial
        $this->assertArrayHasKey('usrTutorialFreeParts', $responseFetchOther);
        $this->assertCount(2, $responseFetchOther['usrTutorialFreeParts']);
        $this->assertEqualsCanonicalizing(
            ['freeTutorial1', 'freeTutorial2'],
            array_column($responseFetchOther['usrTutorialFreeParts'], 'mstTutorialFunctionName')
        );

        $this->assertArrayHasKey('bnidLinkedAt', $responseFetchOther);
        $this->assertEquals($bnidLinkedAt, $responseFetchOther['bnidLinkedAt']);

        $this->assertArrayHasKey('gameStartAt', $responseFetchOther);
        $this->assertEquals($usrUser->game_start_at, $responseFetchOther['gameStartAt']);

        // item
        $this->assertArrayHasKey('usrItems', $responseFetchOther);
        $this->assertCount(3, $usrItems);
        $this->assertEquals($usrItems[0]->getMstItemId(), $responseFetchOther['usrItems'][0]['mstItemId']);
        $this->assertEquals($usrItems[0]->getAmount(), $responseFetchOther['usrItems'][0]['amount']);

        $this->assertArrayHasKey('usrItemTrades', $responseFetchOther);
        $this->assertCount(3, $usrItemTrades);
        $actual = $responseFetchOther['usrItemTrades'][0];
        $this->assertEquals($usrItemTrades[0]->getMstItemId(), $actual['mstItemId']);
        $this->assertEquals($usrItemTrades[0]->getResetTradeAmount(), $actual['tradeAmount']);
        $this->assertEquals(StringUtil::convertToISO8601($usrItemTrades[0]->getTradeAmountResetAt()), $actual['tradeAmountResetAt']);

        // unit
        $this->assertArrayHasKey('usrUnits', $responseFetchOther);
        $this->assertCount(3, $usrUnits);
        $this->assertEquals($usrUnits[0]->getMstUnitId(), $responseFetchOther['usrUnits'][0]['mstUnitId']);
        $this->assertEquals($usrUnits[0]->getId(), $responseFetchOther['usrUnits'][0]['usrUnitId']);
        $this->assertEquals($usrUnits[0]->getLevel(), $responseFetchOther['usrUnits'][0]['level']);
        $this->assertEquals($usrUnits[0]->getRank(), $responseFetchOther['usrUnits'][0]['rank']);
        $this->assertEquals($usrUnits[0]->getGradeLevel(), $responseFetchOther['usrUnits'][0]['gradeLevel']);
        $this->assertEquals($usrUnits[0]->getLastRewardGradeLevel(), $responseFetchOther['usrUnits'][0]['lastRewardGradeLevel']);

        // shop
        $this->assertArrayHasKey('usrStoreProducts', $responseFetchOther);
        $this->assertCount(1, $usrStoreProducts);
        $expected = $usrStoreProducts[0];
        $actual = $responseFetchOther['usrStoreProducts'][0];
        $this->assertEquals($expected->getProductSubId(), $actual['productSubId']);
        $this->assertEquals($expected->getPurchaseCount(), $actual['purchaseCount']);
        $this->assertEquals($expected->getPurchaseTotalCount(), $actual['purchaseTotalCount']);

        $this->assertArrayHasKey('usrShopItems', $responseFetchOther);
        $this->assertCount(1, $responseFetchOther['usrShopItems']);
        $expected = $usrShopItems[0];
        $actual = $responseFetchOther['usrShopItems'][0];
        $this->assertEquals($expected->getMstShopItemId(), $actual['mstShopItemId']);
        $this->assertEquals($expected->getTradeCount(), $actual['tradeCount']);
        $this->assertEquals($expected->getTradeTotalCount(), $actual['tradeTotalCount']);

        // TradePacks
        $this->assertArrayHasKey('usrTradePacks', $responseFetchOther);
        $this->assertCount(1, $responseFetchOther['usrTradePacks']);
        // 日跨ぎでリセットされていることを確認
        foreach ($responseFetchOther['usrTradePacks'] as $tradePack) {
            $this->assertEquals(0, $tradePack['dailyTradeCount']);
        }

        $this->assertArrayHasKey('usrConditionPacks', $responseFetchOther);
        $this->assertCount(1, $usrConditionPacks);
        $expected = $usrConditionPacks[0];
        $actual = $responseFetchOther['usrConditionPacks'][0];
        $this->assertEquals($expected->getMstPackId(), $actual['mstPackId']);
        $this->assertEquals($expected->getStartDate(), $actual['startDate']);

        // idle incentive
        $this->assertArrayHasKey('usrIdleIncentive', $responseFetchOther);
        $this->assertInstanceOf(UsrIdleIncentive::class, $usrIdleIncentive);
        $actual = $responseFetchOther['usrIdleIncentive'];
        $this->assertEquals(0, $actual['diamondQuickReceiveCount']);
        $this->assertEquals(0, $actual['adQuickReceiveCount']);
        $this->assertEquals(StringUtil::convertToISO8601($usrIdleIncentive->getIdleStartedAt()), $actual['idleStartedAt']);
        $this->assertEquals(StringUtil::convertToISO8601($subDayDateTimeString), $actual['diamondQuickReceiveAt']);
        $this->assertEquals(StringUtil::convertToISO8601($subDayDateTimeString), $actual['adQuickReceiveAt']);

        // party
        $this->assertArrayHasKey('usrParties', $responseFetchOther);
        $actual = $responseFetchOther['usrParties'];
        $this->assertCount(1, $responseFetchOther['usrParties']);
        $this->assertEquals($usrParty->getPartyNo(), $actual[0]['partyNo']);
        $this->assertEquals($usrParty->getPartyName(), $actual[0]['partyName']);
        $this->assertEquals($usrParty->getUsrUnitId1(), $actual[0]['usrUnitId1']);
        $this->assertEquals($usrParty->getUsrUnitId2(), $actual[0]['usrUnitId2']);
        $this->assertEquals($usrParty->getUsrUnitId3(), $actual[0]['usrUnitId3']);
        $this->assertEquals($usrParty->getUsrUnitId4(), $actual[0]['usrUnitId4']);
        $this->assertEquals($usrParty->getUsrUnitId5(), $actual[0]['usrUnitId5']);
        $this->assertEquals($usrParty->getUsrUnitId6(), $actual[0]['usrUnitId6']);
        $this->assertEquals($usrParty->getUsrUnitId7(), $actual[0]['usrUnitId7']);
        $this->assertEquals($usrParty->getUsrUnitId8(), $actual[0]['usrUnitId8']);
        $this->assertEquals($usrParty->getUsrUnitId9(), $actual[0]['usrUnitId9']);
        $this->assertEquals($usrParty->getUsrUnitId10(), $actual[0]['usrUnitId10']);

        // artwork party
        $this->assertArrayHasKey('usrArtworkParty', $responseFetchOther);
        $actual = $responseFetchOther['usrArtworkParty'];
        $this->assertEquals($usrArtworkParty->getMstArtworkId1(), $actual['mstArtworkId1']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId2(), $actual['mstArtworkId2']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId3(), $actual['mstArtworkId3']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId4(), $actual['mstArtworkId4']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId5(), $actual['mstArtworkId5']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId6(), $actual['mstArtworkId6']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId7(), $actual['mstArtworkId7']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId8(), $actual['mstArtworkId8']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId9(), $actual['mstArtworkId9']);
        $this->assertEquals($usrArtworkParty->getMstArtworkId10(),$actual['mstArtworkId10']);

        // outpost
        $this->assertArrayHasKey('usrOutposts', $responseFetchOther);
        $actual = $responseFetchOther['usrOutposts'];
        $this->assertCount(2, $actual);
        $this->assertArrayHasKey('mstOutpostId', $actual[0]);
        $this->assertArrayHasKey('isUsed', $actual[0]);

        $this->assertArrayHasKey('usrOutpostEnhancements', $responseFetchOther);
        $actual = $responseFetchOther['usrOutpostEnhancements'];
        $this->assertCount(3, $actual);
        $this->assertArrayHasKey('mstOutpostId', $actual[0]);
        $this->assertArrayHasKey('mstOutpostEnhancementId', $actual[0]);
        $this->assertArrayHasKey('level', $actual[0]);

        // stage
        $this->assertArrayHasKey('usrInGameStatus', $responseFetchOther);
        $actual = $responseFetchOther['usrInGameStatus'];
        $this->assertCount(6, $actual);
        $this->assertArrayHasKey('inGameContentType', $actual);
        $this->assertArrayHasKey('targetMstId', $actual);
        $this->assertArrayHasKey('isStartedSession', $actual);
        $this->assertArrayHasKey('partyNo', $actual);
        $this->assertArrayHasKey('continueCount', $actual);
        $this->assertArrayHasKey('continueAdCount', $actual);

        // encyclopedia
        $this->assertArrayHasKey('usrArtworks', $responseFetchOther);
        $actual = $responseFetchOther['usrArtworks'];
        $this->assertCount(1, $actual);
        $this->assertArrayHasKey('mstArtworkId', $actual[0]);
        $this->assertArrayHasKey('usrArtworkFragments', $responseFetchOther);
        $actual = $responseFetchOther['usrArtworkFragments'];
        $this->assertCount(1, $actual);
        $this->assertArrayHasKey('mstArtworkId', $actual[0]);
        $this->assertArrayHasKey('mstArtworkFragmentId', $actual[0]);
        $actual = $responseFetchOther['usrReceivedUnitEncyclopediaRewards'];
        $this->assertCount(2, $actual);
        foreach ($actual as $item) {
            $this->assertContains($item['mstUnitEncyclopediaRewardId'], $receivedMstUnitEncyclopediaRewardIds);
        }

        // ingame
        $this->assertArrayHasKey('usrEnemyDiscoveries', $responseFetchOther);
        $actual = $responseFetchOther['usrEnemyDiscoveries'];
        $this->assertCount(1, $actual);
        $this->assertArrayHasKey('mstEnemyCharacterId', $actual[0]);
        $this->assertEquals('enemy1', $actual[0]['mstEnemyCharacterId']);

        // mission
        $this->assertArrayHasKey('dailyBonusRewards', $responseFetchOther);
        $actuals = collect($responseFetchOther['dailyBonusRewards'])->keyBy('missionType');
        $this->assertCount(1, $actuals);
        $actual = $actuals[MissionDailyBonusType::DAILY_BONUS->value];
        $this->assertNotNull($actual);
        $this->assertEquals(RewardType::COIN->value, $actual['reward']['resourceType']);
        $this->assertEquals(200, $actual['reward']['resourceAmount']);
        $this->assertArrayHasKey('eventDailyBonusRewards', $responseFetchOther);
        $actual = $responseFetchOther['eventDailyBonusRewards'];
        $this->assertNotNull($actual);
        $this->assertEquals('schedule_1', $actual[0]['mstMissionEventDailyBonusScheduleId']);
        $this->assertEquals(1, $actual[0]['loginDayCount']);
        $this->assertEquals(RewardType::COIN->value, $actual[0]['reward']['resourceType']);
        $this->assertEquals(100, $actual[0]['reward']['resourceAmount']);
        $actual = $responseFetchOther['usrMissionEventDailyBonusProgresses'];
        $this->assertEquals('schedule_1', $actual[0]['mstMissionEventDailyBonusScheduleId']);
        $this->assertEquals(1, $actual[0]['progress']);
        $this->assertArrayHasKey('comebackBonusRewards', $responseFetchOther);
        $actual = $responseFetchOther['comebackBonusRewards'];
        $this->assertNotNull($actual);
        $this->assertEquals('schedule_1', $actual[0]['mstComebackBonusScheduleId']);
        $this->assertEquals(1, $actual[0]['loginDayCount']);
        $this->assertEquals(RewardType::COIN->value, $actual[0]['reward']['resourceType']);
        $this->assertEquals(300, $actual[0]['reward']['resourceAmount']);
        $actual = $responseFetchOther['usrComebackBonusProgresses'];
        $this->assertEquals('schedule_1', $actual[0]['mstComebackBonusScheduleId']);
        $this->assertEquals(1, $actual[0]['progress']);
        $this->assertEquals('2025-06-12T19:00:00+00:00', $actual[0]['startAt']);
        $this->assertEquals('2025-06-17T18:59:59+00:00', $actual[0]['endAt']);

        // IGN
        $this->assertArrayHasKey('oprInGameNotices', $responseFetchOther);
        $actuals = collect($responseFetchOther['oprInGameNotices']);
        $this->assertCount(2, $actuals);
        $this->assertEqualsCanonicalizing(['ign1', 'ign2'], $actuals->pluck('id')->toArray());

        // 年齢確認
        $this->assertArrayHasKey('usrStoreInfo', $responseFetchOther);
        $actual = $responseFetchOther['usrStoreInfo'];
        // 生年月日未登録なのでnull(年齢未登録)
        $this->assertEquals(20, $actual['age']);

        // キャンペーン
        $this->assertArrayHasKey('oprCampaigns', $responseFetchOther);
        $actual = $responseFetchOther['oprCampaigns'];
        $this->assertCount(1, $actual);

        // 降臨バトル
        $this->assertArrayHasKey('adventBattleRaidTotalScore', $responseFetchOther);
        $actual = $responseFetchOther['adventBattleRaidTotalScore'];
        $this->assertEquals($mstAdventBattleId, $actual['mstAdventBattleId']);
        $this->assertEquals(0, $actual['totalDamage']);

        // ガシャ
        $this->assertArrayHasKey('usrGachas', $responseFetchOther);
        $actual = $responseFetchOther['usrGachas'];
        $this->assertCount(1, $actual);
        $this->assertEquals($gachaId, $actual[0]['oprGachaId']);
        $this->assertEquals(StringUtil::convertToISO8601($usrGacha->getExpiresAt()), $actual[0]['expiresAt']);

        // Pvp
        $seasonId = sprintf('%04d0%02d', $now->isoWeekYear(), $now->isoWeek());
        $this->assertArrayHasKey('sysPvpSeason', $responseFetchOther);
        $actual = $responseFetchOther['sysPvpSeason'];
        $this->assertEquals($seasonId, $actual['id']);
        $this->assertArrayHasKey('startAt', $actual);
        $this->assertArrayHasKey('endAt', $actual);
        $this->assertArrayHasKey('closedAt', $actual);

        // PvpStatus
        $this->assertArrayHasKey('usrPvpStatus', $responseFetchOther);
        $actualPvpStatus = $responseFetchOther['usrPvpStatus'];
        $this->assertEquals($usrPvp->getScore(), $actualPvpStatus['score']);
        $this->assertEquals($usrPvp->getPvpRankClassType(), $actualPvpStatus['pvpRankClassType']);
        $this->assertEquals($usrPvp->getPvpRankClassLevel(), $actualPvpStatus['pvpRankClassLevel']);
        $this->assertEquals($mstPvp->getMaxDailyChallengeCount(), $actualPvpStatus['dailyRemainingChallengeCount']);
        $this->assertEquals($mstPvp->getMaxDailyItemChallengeCount(), $actualPvpStatus['dailyRemainingItemChallengeCount']);

        // mngContentCloses
        $this->assertArrayHasKey('mngContentCloses', $responseFetchOther);
        $actualContentCloses = $responseFetchOther['mngContentCloses'];
        $this->assertIsArray($actualContentCloses);
        // is_valid=1のレコードのみが含まれること（content_close_1, content_close_2, content_close_3の3件）
        $this->assertCount(3, $actualContentCloses);

        $contentCloseIds = array_column($actualContentCloses, 'id');
        $this->assertContains('content_close_1', $contentCloseIds);
        $this->assertContains('content_close_2', $contentCloseIds);
        $this->assertContains('content_close_3', $contentCloseIds);
        $this->assertNotContains('content_close_4', $contentCloseIds); // is_valid=0なので含まれない

        foreach ($actualContentCloses as $contentClose) {
            $this->assertArrayHasKey('id', $contentClose);
            $this->assertArrayHasKey('contentType', $contentClose);
            $this->assertArrayHasKey('startAt', $contentClose);
            $this->assertArrayHasKey('endAt', $contentClose);
            $this->assertArrayHasKey('isValid', $contentClose);
        }

        // WebStore購入通知
        $this->assertArrayHasKey('webstorePurchaseProductSubIds', $responseFetchOther);
        $actualProductSubIds = $responseFetchOther['webstorePurchaseProductSubIds'];
        $this->assertIsArray($actualProductSubIds);
        $this->assertCount(2, $actualProductSubIds);
        $this->assertContains($productSubId1, $actualProductSubIds);
        $this->assertContains($productSubId2, $actualProductSubIds);

        // Redis確認: 通知がクリアされていること
        $clearedNotifications = $this->getFromRedis($cacheKey);
        $this->assertNull($clearedNotifications, 'WebStore購入通知がRedisからクリアされていること');

        // DB確認

        // idle incentive

        // クイック探索回数がリセットされている
        $usrIdleIncentive->refresh();
        $this->assertEquals(0, $usrIdleIncentive->getDiamondQuickReceiveCount());
        $this->assertEquals(0, $usrIdleIncentive->getAdQuickReceiveCount());
        $this->assertEquals($subDayDateTimeString, $usrIdleIncentive->getDiamondQuickReceiveAt());
        $this->assertEquals($subDayDateTimeString, $usrIdleIncentive->getAdQuickReceiveAt());

        // mission
        // デイリーボーナス
        $usrMissions = $this->getUsrMissions($usrUser->getId(), MissionType::DAILY_BONUS);
        $this->checkUsrMissionStatus(
            $usrMissions,
            'dailyBonus-1',
            isExist: true,
            isClear: true,
            clearedAt: $now->toDateTimeString(),
            isReceiveReward: true,
            receivedRewardAt: $now->toDateTimeString(),
        );

        $this->assertDatabaseHas('usr_webstore_infos', [
            'usr_user_id' => $usrUser->getId(),
            'country_code' => 'JP',
            'os_platform' => System::PLATFORM_IOS,
            'ad_id' => null,
        ]);
    }

    public function testFetch_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->generateUser($now);
        $usrUserParameter = $this->generateUserParameter($usrUser);

        [$usrStages, $usrStageEvents, , $usrItems,] = $this->generateStageItemUnit($usrUser);
        $usrAdventBattles = $this->generateAdventBattle($usrUser);

        $usrUserBuyCount = UsrUserBuyCount::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'daily_buy_stamina_ad_count' => 1,
            'daily_buy_stamina_ad_at' => $now->toDateTimeString(),
        ]);

        $gameFetchData = new GameFetchData(
            new UsrParameterData(
                $usrUserParameter->getLevel(),
                $usrUserParameter->getExp(),
                $usrUserParameter->getCoin(),
                $usrUserParameter->getStamina(),
                $usrUserParameter->getStaminaUpdatedAt(),
                6,
                7,
                8,
            ),
            $usrStages,
            $usrStageEvents,
            collect(),
            $usrAdventBattles,
            new GameBadgeData(123, 456, 225, collect(['mstEventId' => 3, 'mstEventId2' => 1]), 999, collect()),
            $usrUserBuyCount,
            new MissionStatusData(false),
        );
        $resultData = new GameFetchResultData($gameFetchData);

        $this->mock(GameFetchUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $response = $this->withHeaders([
            System::HEADER_LANGUAGE => Language::Ja->value,
        ])->sendGetRequest('fetch');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $gameFetchData = $resultData->gameFetchData;

        $this->assertArrayHasKey('fetch', $response);
        $responseFetch = $response['fetch'];

        $this->assertArrayHasKey('usrParameter', $responseFetch);
        $this->assertEquals($gameFetchData->usrUserParameter->getLevel(), $responseFetch['usrParameter']['level']);
        $this->assertEquals($gameFetchData->usrUserParameter->getExp(), $responseFetch['usrParameter']['exp']);
        $this->assertEquals($gameFetchData->usrUserParameter->getCoin(), $responseFetch['usrParameter']['coin']);
        $this->assertEquals($gameFetchData->usrUserParameter->getStamina(), $responseFetch['usrParameter']['stamina']);
        $this->assertEquals(StringUtil::convertToISO8601($gameFetchData->usrUserParameter->getStaminaUpdatedAt()), $responseFetch['usrParameter']['staminaUpdatedAt']);
        $this->assertEquals($gameFetchData->usrUserParameter->getFreeDiamond(), $responseFetch['usrParameter']['freeDiamond']);
        $this->assertEquals($gameFetchData->usrUserParameter->getPaidDiamondIos(), $responseFetch['usrParameter']['paidDiamondIos']);
        $this->assertEquals($gameFetchData->usrUserParameter->getPaidDiamondAndroid(), $responseFetch['usrParameter']['paidDiamondAndroid']);

        $this->assertArrayHasKey('usrStages', $responseFetch);
        $this->assertCount(3, $gameFetchData->usrStages);
        $this->assertEquals($gameFetchData->usrStages[0]->getMstStageId(), $responseFetch['usrStages'][0]['mstStageId']);
        $this->assertEquals($gameFetchData->usrStages[0]->getClearTimeMs(), $responseFetch['usrStages'][0]['clearTimeMs']);
        $this->assertEquals($gameFetchData->usrStages[0]->getClearCount(), $responseFetch['usrStages'][0]['clearCount']);

        $this->assertArrayHasKey('usrStageEvents', $responseFetch);
        $this->assertCount(3, $gameFetchData->usrStageEvents);
        $this->assertEquals($gameFetchData->usrStageEvents[0]->getMstStageId(), $responseFetch['usrStageEvents'][0]['mstStageId']);
        $this->assertEquals($gameFetchData->usrStageEvents[0]->getResetClearCount(), $responseFetch['usrStageEvents'][0]['resetClearCount']);
        $this->assertEquals($gameFetchData->usrStageEvents[0]->getResetAdChallengeCount(), $responseFetch['usrStageEvents'][0]['resetAdChallengeCount']);
        $this->assertEquals($gameFetchData->usrStageEvents[0]->getClearCount(), $responseFetch['usrStageEvents'][0]['totalClearCount']);
        $this->assertEquals(StringUtil::convertToISO8601($gameFetchData->usrStageEvents[0]->getLastChallengedAt()), $responseFetch['usrStageEvents'][0]['lastChallengedAt']);

        $this->assertArrayHasKey('badges', $responseFetch);
        $this->assertEquals(123, $responseFetch['badges']['unreceivedMissionRewardCount']);
        $this->assertEquals(456, $responseFetch['badges']['unreceivedMissionBeginnerRewardCount']);
        $this->assertEquals(225, $responseFetch['badges']['unopenedMessageCount']);
        $this->assertEquals('mstEventId', $responseFetch['badges']['unreceivedMissionEventRewardCounts'][0]['mstEventId']);
        $this->assertEquals(3, $responseFetch['badges']['unreceivedMissionEventRewardCounts'][0]['count']);
        $this->assertEquals(999, $responseFetch['badges']['unreceivedMissionAdventBattleRewardCount']);

        $this->assertArrayHasKey('missionStatus', $responseFetch);
        $this->assertFalse($responseFetch['missionStatus']['isBeginnerMissionCompleted']);

        // usrAdventBattles
        $this->assertArrayHasKey('usrAdventBattles', $responseFetch);
        $this->assertCount(3, $responseFetch['usrAdventBattles']);
        foreach ($responseFetch['usrAdventBattles'] as $index => $adventBattle) {
            $expected = $usrAdventBattles[$index];
            $this->assertEquals($expected->getMstAdventBattleId(), $adventBattle['mstAdventBattleId']);
            $this->assertEquals($expected->getMaxScore(), $adventBattle['maxScore']);
            $this->assertEquals($expected->getTotalScore(), $adventBattle['totalScore']);
            $this->assertEquals($expected->getResetChallengeCount(), $adventBattle['resetChallengeCount']);
            $this->assertEquals($expected->getResetAdChallengeCount(), $adventBattle['resetAdChallengeCount']);
            $this->assertEquals($expected->getClearCount(), $adventBattle['clearCount']);
        }
    }

    public function testServerTime_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $serverTime = CarbonImmutable::now();
        $resultData = new GameServerTimeResultData($serverTime);

        $this->mock(GameServerTimeUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('__invoke')->andReturn($resultData);
        });

        // Exercise
        $response = $this->sendGetRequest('server_time');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertEquals(StringUtil::convertToISO8601($resultData->serverTime->toDateTimeString()), $response['serverTime']);
    }

    public function testBadge_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $now = $this->fixTime();
        $usrUser = $this->generateUser($now);

        $mngContentCloses = MngContentClose::factory()->count(4)->create([
            'is_valid' => 1,
        ])->map(function ($item) {
            return $item->toEntity();
        });

        $gameBadgeData = new GameBadgeData(
            123,
            456,
            225,
            collect(['mstEventId' => 3]),
            999,
            collect(['artwork_panel_mission_001' => 2])
        );
        $resultData = new GameBadgeResultData($gameBadgeData, $mngContentCloses);

        $this->mock(GameBadgeUseCase::class, function (MockInterface $mock) use ($resultData) {
            $mock->shouldReceive('exec')->andReturn($resultData);
        });

        // Exercise
        $response = $this->withHeaders([
            System::HEADER_LANGUAGE => Language::Ja->value,
        ])->sendGetRequest('badge');

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $this->assertArrayHasKey('badges', $response);
        $badges = $response['badges'];
        $this->assertEquals(123, $badges['unreceivedMissionRewardCount']);
        $this->assertEquals(456, $badges['unreceivedMissionBeginnerRewardCount']);
        $this->assertEquals(225, $badges['unopenedMessageCount']);
        $this->assertEquals('mstEventId', $badges['unreceivedMissionEventRewardCounts'][0]['mstEventId']);
        $this->assertEquals(3, $badges['unreceivedMissionEventRewardCounts'][0]['count']);
        $this->assertEquals(999, $badges['unreceivedMissionAdventBattleRewardCount']);
        $this->assertEquals('artwork_panel_mission_001', $badges['unreceivedMissionArtworkPanelRewardCounts'][0]['mstArtworkPanelMissionId']);
        $this->assertEquals(2, $badges['unreceivedMissionArtworkPanelRewardCounts'][0]['unreceivedMissionRewardCount']);
    }
}
