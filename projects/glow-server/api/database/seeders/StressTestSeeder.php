<?php

namespace Database\Seeders;

use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Emblem\Models\UsrEmblem;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\IdleIncentive\Models\UsrIdleIncentive;
use App\Domain\Item\Models\UsrItem;
use App\Domain\Message\Models\UsrMessage;
use App\Domain\Mission\Models\UsrMissionDailyBonus;
use App\Domain\Mission\Models\UsrMissionEvent;
use App\Domain\Mission\Models\UsrMissionEventDaily;
use App\Domain\Mission\Models\UsrMissionEventDailyBonus;
use App\Domain\Mission\Models\UsrMissionEventDailyBonusProgress;
use App\Domain\Mission\Models\UsrMissionEventDailyProgress;
use App\Domain\Mission\Models\UsrMissionEventProgress;
use App\Domain\Mission\Models\UsrMissionLimitedTerm;
use App\Domain\Mission\Models\UsrMissionLimitedTermProgress;
use App\Domain\Mission\Models\UsrMissionRecentAddition;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Mission2\Models\UsrMissionNormal;
use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Party\Models\UsrParty;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\Shop\Models\UsrStoreProduct;
use App\Domain\Stage\Models\UsrStage;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Unit\Models\UsrUnit;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserBuyCount;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Currency\Delegators\CurrencyDelegator;

class StressTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // USER_COUNT=200 php artisan db:seed --class=StressTestSeeder
        $userCount = (int) env('USER_COUNT', 100);
        $now = now()->toDateTimeString();
        echo "start : $now\n";
        foreach (range(1, $userCount) as $i) {
            // my_idで重複してエラーになることがあるためエラーになっても処理を継続させるためにtry-catchでやりすごす
            try {
                $this->generateUserData();
            } catch (\Throwable $e) {
                echo 'Error occurred: ' . $e->getMessage() . "\n";
            }
            if ($i % 100 === 0) {
                echo "Generated {$i} users\n";
            }
        }
        $now = now()->toDateTimeString();
        echo "end   : $now\n";
    }

    private function generateUserData(): void
    {
        // 指定しているIDは負荷テストのためのシナリオを実行して作成されたユーザーデータを参考にしたものです。
        $now = now()->toDateTimeString();
        $currencyDelegator = app(CurrencyDelegator::class);
        $initialUnits = [
            'chara_spy_00001',
            'chara_spy_00301',
            'chara_dan_00001',
            'chara_dan_00101',
            'chara_gom_00001',
            'chara_chi_00001',
            'chara_chi_00301',
            'chara_kai_00001',
            'chara_kai_00002',
            'chara_sur_00301',
            'chara_sur_00401',
            'chara_sum_00101'
        ];
        $usrUser = UsrUser::factory()->make();
        $usrDevice = UsrDevice::factory()->make(['usr_user_id' => $usrUser->id]);
        $usrUserParameter = UsrUserParameter::factory()->make(['usr_user_id' => $usrUser->id]);
        $usrUserProfile = UsrUserProfile::factory()->make([
            'usr_user_id' => $usrUser->id,
            'name' => fake()->name(),
            'my_id' => 'A' . random_int(1000000000, 9999999999),
            'mst_unit_id' => 'chara_spy_00001',
            'mst_emblem_id' => '',
        ]);
        $usrUnits = [];
        foreach (range(0, 11) as $i) {
            $usrUnits[] = UsrUnit::factory()->make([
                'usr_user_id' => $usrUser->id,
                'mst_unit_id' => $initialUnits[$i]
            ])->toArray();
        }

        $usrParties = [];
        foreach (range(1, 10) as $i) {
            $usrParties[] = UsrParty::factory()->make([
                'usr_user_id' => $usrUser->id,
                'party_no' => $i,
                'party_name' => "party{$i}",
                'usr_unit_id_1' => $usrUnits[0]['id'],
                'usr_unit_id_2' => $usrUnits[1]['id'],
                'usr_unit_id_3' => $usrUnits[2]['id'],
                'usr_unit_id_4' => $usrUnits[3]['id'],
                'usr_unit_id_5' => $usrUnits[4]['id'],
                'usr_unit_id_6' => $usrUnits[5]['id'],
                'usr_unit_id_7' => $usrUnits[6]['id'],
                'usr_unit_id_8' => $usrUnits[7]['id'],
                'usr_unit_id_9' => $usrUnits[8]['id'],
                'usr_unit_id_10' => $usrUnits[9]['id']
            ])->toArray();
        }
        $usrOutpost = UsrOutpost::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_outpost_id' => 'outpost_1',
            'is_used' => 1
        ]);
        $usrIdleIncentive = UsrIdleIncentive::factory()->make([
            'usr_user_id' => $usrUser->id,
            'diamond_quick_receive_count' => 0,
            'ad_quick_receive_count' => 0,
            'idle_started_at' => $now,
            'diamond_quick_receive_at' => $now,
            'ad_quick_receive_at' => $now
        ]);
        $usrMissionStatus = UsrMissionStatus::factory()->make([
            'usr_user_id' => $usrUser->id,
            'beginner_mission_status' => 0,
            'latest_mst_hash' => '',
            'mission_unlocked_at' => $now
        ]);
        $currencyDelegator->createUser(
            userId: $usrUser->id,
            osPlatform: 'iOS',
            billingPlatform: 'AppStore',
            freeAmount: 0,
        );
        $usrStoreInfo = UsrStoreInfo::factory()->make([
            'usr_user_id' => $usrUser->id,
            'created_at' => $now,
            'updated_at' => $now
        ]);


        $usrAdventBattleSession = UsrAdventBattleSession::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_advent_battle_id' => 'advent_battle_spy_01',
            'is_valid' => 0,
            'party_no' => 1,
            'battle_start_at' => $now
        ]);
        $score = fake()->numberBetween(1, 100000);
        $usrAdventBattle = UsrAdventBattle::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_advent_battle_id' => 'advent_battle_spy_01',
            'max_score' => $score,
            'total_score' => $score,
            'challenge_count' => 1,
            'reset_challenge_count' => 1,
            'reset_ad_challenge_count' => 0,
            'max_received_max_score_reward' => $score,
            'is_ranking_reward_received' => 0,
            'latest_reset_at' => $now
        ]);
        $usrArtwork = UsrArtwork::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_artwork_id' => 'artwork_dan_0001',
        ]);
        $usrArtworkFragment = UsrArtworkFragment::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_artwork_id' => 'artwork_spy_0001',
            'mst_artwork_fragment_id' => fake()->randomElement(['artwork_fragment_spy_00101', 'artwork_fragment_spy_00102']),
        ]);
        $usrEmblem = UsrEmblem::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_emblem_id' => 'emblem_event_dan_00002',
        ]);
        $usrGachaUpper = UsrGachaUpper::factory()->make([
            'usr_user_id' => $usrUser->id,
            'upper_group' => 'Festival1',
            'upper_type' => 'Pickup',
            'count' => 10
        ]);
        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => '10',
            'played_at' => $now,
            'count' => 10,
            'daily_count' => 10
        ]);
        $itemIds = [
            'item_icon_piece_dan_00001',
            'box_glo_00005',
            'memory_glo_00001'
        ];
        $usrItems = [];
        foreach ($itemIds as $itemId) {
            $usrItems[] = UsrItem::factory()->make([
                'usr_user_id' => $usrUser->id,
                'mst_item_id' => $itemId,
                'amount' => 10
            ])->toArray();
        }
        $usrMessage = UsrMessage::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mng_message_id' => '9e0b5402-f6ad-4af6-b5c0-7214c84b19ec',
            'message_source' => 'MngMessage',
            'created_at' => $now
        ]);
        $usrMissionDailyBonus = UsrMissionDailyBonus::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_mission_daily_bonus_id' => 'daily_bonus_1',
            'status' => 2,
            'cleared_at' => $now,
            'received_reward_at' => $now
        ]);
        $usrMissionEventDaily = UsrMissionEventDaily::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_mission_event_daily_id' => 'event_daily_1',
            'status' => 1,
            'cleared_at' => $now,
            'received_reward_at' => null,
            'latest_update_at' => $now
        ]);
        $eventDailyCriterionKeys = [
            'SpecificSeriesEmblemAcquiredCount:dan:event_jig_00001',
            'SpecificSeriesUnitAcquiredCount:jig:event_jig_00001',
            'SpecificUnitLevel:chara_jig_00301:event_jig_00001',
            'UnitAcquiredCount::event_jig_00001',
            'SpecificUnitAcquiredCount:chara_jig_00301:event_spy_00001',
            'EmblemAcquiredCount::event_jig_00001',
            'UnitLevel::event_spy_00001',
            'SpecificEmblemAcquiredCount:emblem_event_dan_00002:event_spy_00001',
            'SpecificUnitAcquiredCount:chara_jig_00301:event_jig_00001',
            'UnitLevel::event_jig_00001',
            'SpecificUnitLevel:chara_jig_00301:event_spy_00001',
            'LoginCount::event_spy_00001',
            'UnitAcquiredCount::event_spy_00001',
            'LoginCount::event_jig_00001',
            'SpecificSeriesEmblemAcquiredCount:dan:event_spy_00001',
            'SpecificSeriesUnitAcquiredCount:jig:event_spy_00001',
            'EmblemAcquiredCount::event_spy_00001',
            'SpecificEmblemAcquiredCount:emblem_event_dan_00002:event_jig_00001'
        ];
        $dailyProgresses = [];
        foreach ($eventDailyCriterionKeys as $criterionKey) {
            $dailyProgresses[] = UsrMissionEventDailyProgress::factory()->make([
                'usr_user_id' => $usrUser->id,
                'criterion_key' => $criterionKey,
                'progress' => 1,
                'latest_update_at' => $now,
            ])->toArray();
        }
        $usrMissionEventDailyBonus = UsrMissionEventDailyBonus::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_mission_event_daily_bonus_id' => 'daily_event_jig_bonus_1',
            'status' => 2,
            'cleared_at' => $now,
            'received_reward_at' => $now
        ]);
        $usrMissionEventDailyBonusProgress = UsrMissionEventDailyBonusProgress::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_mission_event_daily_bonus_schedule_id' => 'event_jig_daily_bonus',
            'progress' => 1,
            'latest_update_at' => $now
        ]);
        $usrMissionEvents = [];
        foreach (range(1, 5) as $i) {
            $usrMissionEvents[] = UsrMissionEvent::factory()->make([
                'usr_user_id' => $usrUser->id,
                'mst_mission_event_id' => "event_{$i}",
                'status' => 1,
                'cleared_at' => $now,
                'received_reward_at' => $now
            ])->toArray();
        }
        $usrMissionEventCriterionKeys = [
            'SpecificItemCollect:box_glo_00001:event_jig_00001',
            'SpecificItemCollect:item_icon_piece_dan_00001:event_spy_00001',
            'UnitAcquiredCount::event_spy_00001',
            'UnitLevelUpCount::event_spy_00001',
            'SpecificUnitRankUpCount:chara_dan_00001:event_jig_00001',
            'SpecificSeriesUnitAcquiredCount:jig:event_spy_00001',
            'SpecificUnitGradeUpCount:chara_dan_00001:event_jig_00001',
            'SpecificUnitLevel:chara_jig_00301:event_spy_00001',
            'CoinCollect::event_spy_00001',
            'SpecificEmblemAcquiredCount:emblem_event_jig_00003:event_jig_00001',
            'CoinUsedCount::event_jig_00001',
            'SpecificUnitAcquiredCount:chara_jig_00301:event_jig_00001',
            'SpecificOutpostEnhanceLevel:enhance_1_3:event_spy_00001',
            'SpecificUnitLevel:chara_dan_00001:event_jig_00001',
            'SpecificItemCollect:memory_glo_00001:event_jig_00001',
            'SpecificUnitGradeUpCount:chara_dan_00001:event_spy_00001',
            'SpecificUnitLevel:chara_gom_00101:event_jig_00001',
            'SpecificEmblemAcquiredCount:emblem_event_dan_00002:event_jig_00001',
            'SpecificEmblemAcquiredCount:emblem_event_dan_00002:event_spy_00001',
            'SpecificUnitAcquiredCount:chara_gom_00101:event_spy_00001',
            'SpecificUnitLevel:chara_dan_00001:event_spy_00001',
            'UnitAcquiredCount::event_jig_00001',
            'GachaDrawCount::event_jig_00001',
            'SpecificSeriesUnitAcquiredCount:gom:event_spy_00001',
            'SpecificSeriesEmblemAcquiredCount:dan:event_jig_00001',
            'SpecificStageChallengeCount:normal_spy_00001:event_jig_00001',
        ];
        $usrMissionEventProgresses = [];
        foreach ($usrMissionEventCriterionKeys as $criterionKey) {
            $usrMissionEventProgresses[] = UsrMissionEventProgress::factory()->make([
                'usr_user_id' => $usrUser->id,
                'criterion_key' => $criterionKey,
                'progress' => 1,
            ])->toArray();
        }
        $mstMissionLimitedTermIds = [
            'limited_term_1',
            'limited_term_2',
            'limited_term_14',
            'limited_term_15',
            'limited_term_16',
            'limited_term_17',
            'limited_term_18',
            'limited_term_19',
            'limited_term_20',
            'limited_term_21',
            'limited_term_30',
            'limited_term_32',
            'limited_term_31',
        ];
        $usrMissionLimitedTerms = [];
        foreach ($mstMissionLimitedTermIds as $mstMissionLimitedTermId) {
            $usrMissionLimitedTerms[] = UsrMissionLimitedTerm::factory()->make([
                'usr_user_id' => $usrUser->id,
                'mst_mission_limited_term_id' => $mstMissionLimitedTermId,
                'status' => 1,
                'cleared_at' => $now,
                'received_reward_at' => $now
            ])->toArray();
        }
        $usrMissionLimitedTermCriterionKeys = [
            'AdventBattleChallengeCount::group1',
            'AdventBattleScore::group2',
            'AdventBattleScore::group3',
            'AdventBattleTotalScore::group2',
            'AdventBattleTotalScore::group3',
            'CoinCollect::group2',
            'CoinCollect::group3',
            'SpecificItemCollect:box_glo_00003:group2',
            'SpecificItemCollect:box_glo_00003:group3',
            'UserLevel::group2',
            'UserLevel::group3',
        ];
        $usrMissionLimitedTermProgresses = [];
        foreach ($usrMissionLimitedTermCriterionKeys as $criterionKey) {
            $usrMissionLimitedTermProgresses[] = UsrMissionLimitedTermProgress::factory()->make([
                'usr_user_id' => $usrUser->id,
                'criterion_key' => $criterionKey,
                'progress' => 1,
            ])->toArray();
        }
        $mstMissionNormals = [
            ['id' => 'achievement_1', 'type' => 1],
            ['id' => 'achievement_2', 'type' => 1],
            ['id' => 'achievement_3', 'type' => 1],
            ['id' => 'achievement_4', 'type' => 1],
            ['id' => 'achievement_5', 'type' => 1],
            ['id' => 'achievement_56', 'type' => 1],
            ['id' => 'achievement_6', 'type' => 1],
            ['id' => 'achievement_62', 'type' => 1],
            ['id' => 'achievement_7', 'type' => 1],
            ['id' => 'beginner_11', 'type' => 2],
            ['id' => 'beginner_21', 'type' => 2],
            ['id' => 'beginner_31', 'type' => 2],
            ['id' => 'beginner_41', 'type' => 2],
            ['id' => 'beginner_51', 'type' => 2],
            ['id' => 'beginner_61', 'type' => 2],
            ['id' => 'beginner_62', 'type' => 2],
            ['id' => 'beginner_71', 'type' => 2],
            ['id' => 'daily_1', 'type' => 3],
            ['id' => 'weekly_1', 'type' => 5],
        ];
        $usrMissionNormals = [];
        foreach ($mstMissionNormals as $mstMissionNormal) {
            $usrMissionNormals[] = UsrMissionNormal::factory()->make([
                'usr_user_id' => $usrUser->id,
                'mission_type' => $mstMissionNormal['type'],
                'mst_mission_id' => $mstMissionNormal['id'],
                'status' => 1,
                'is_open' => 1,
                'progress' => 1,
                'unlock_progress' => 0,
                'cleared_at' => $now,
                'received_reward_at' => $now
            ])->toArray();
        }
        $missionTypes = ['Event', 'EventDaily'];
        $usrMissionRecentAdditions = [];
        foreach ($missionTypes as $missionType) {
            $usrMissionRecentAdditions[] = UsrMissionRecentAddition::factory()->make([
                'usr_user_id' => $usrUser->id,
                'mission_type' => $missionType,
                'latest_release_key' => 1,
            ])->toArray();
        }
        $usrOutpostEnhancement = UsrOutpostEnhancement::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_outpost_id' => 'enhance_1_3',
            'level' => 2,
        ]);
        $usrShopItem = UsrShopItem::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_shop_item_id' => 'Coin_13',
            'trade_count' => 1,
            'trade_total_count' => 1,
            'last_reset_at' => $now,
        ]);
        $usrShopPass = UsrShopPass::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_shop_pass_id' => '999',
            'daily_reward_received_count' => 0,
            'daily_latest_received_at' => $now,
            'start_at' => $now,
            'end_at' => $now,
        ]);
        $usrStage = UsrStage::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_stage_id' => 'normal_spy_00001',
            'clear_status' => 1,
            'clear_count' => 1,
        ]);
        $usrStageSession = UsrStageSession::factory()->make([
            'usr_user_id' => $usrUser->id,
            'mst_stage_id' => 'normal_spy_00001',
            'is_valid' => 1,
            'party_no' => 1,
            'continue_count' => 0,
            'daily_continue_ad_count' => 0,
            'latest_reset_at' => $now,
        ]);
        $productSubIds = ['6', '999'];
        $usrStoreProducts = [];
        foreach ($productSubIds as $productSubId) {
            $usrStoreProducts[] = UsrStoreProduct::factory()->make([
                'usr_user_id' => $usrUser->id,
                'product_sub_id' => $productSubId,
                'purchase_count' => 1,
                'purchase_total_count' => 1,
                'last_reset_at' => $now,
            ])->toArray();
        }
        $usrUserBuyCount = UsrUserBuyCount::factory()->make([
            'usr_user_id' => $usrUser->id,
            'daily_buy_stamina_ad_count' => 1,
            'daily_buy_stamina_ad_at' => $now,
        ]);
        $usrUserLogin = UsrUserLogin::factory()->make([
            'usr_user_id' => $usrUser->id,
            'first_login_at' => $now,
            'last_login_at' => $now,
            'hourly_accessed_at' => $now,
            'login_count' => 1,
            'login_day_count' => 1,
            'login_continue_day_count' => 1,
            'comeback_day_count' => 0,
        ]);
        DB::transaction(function () use (
            $usrUser,
            $usrDevice,
            $usrUserParameter,
            $usrUserProfile,
            $usrUnits,
            $usrParties,
            $usrOutpost,
            $usrIdleIncentive,
            $usrMissionStatus,
            $usrStoreInfo,
            $usrAdventBattleSession,
            $usrAdventBattle,
            $usrArtwork,
            $usrArtworkFragment,
            $usrEmblem,
            $usrGachaUpper,
            $usrItems,
            $usrMessage,
            $usrMissionDailyBonus,
            $usrMissionEventDaily,
            $dailyProgresses,
            $usrMissionEventDailyBonus,
            $usrMissionEventDailyBonusProgress,
            $usrMissionEvents,
            $usrMissionEventProgresses,
            $usrMissionLimitedTerms,
            $usrMissionLimitedTermProgresses,
            $usrMissionNormals,
            $usrMissionRecentAdditions,
            $usrOutpostEnhancement,
            $usrShopItem,
            $usrShopPass,
            $usrStage,
            $usrStageSession,
            $usrStoreProducts,
            $usrUserBuyCount,
            $usrUserLogin,
        ) {
            UsrUser::insert($usrUser->toArray());
            UsrDevice::insert($usrDevice->toArray());
            UsrUserParameter::insert($usrUserParameter->toArray());
            UsrUserProfile::insert($usrUserProfile->toArray());
            UsrUnit::insert($usrUnits);
            UsrParty::insert($usrParties);
            UsrOutpost::insert($usrOutpost->toArray());
            UsrIdleIncentive::insert($usrIdleIncentive->toArray());
            UsrMissionStatus::insert($usrMissionStatus->toArray());
            UsrStoreInfo::insert($usrStoreInfo->toArray());
            UsrAdventBattleSession::insert($usrAdventBattleSession->toArray());
            UsrAdventBattle::insert($usrAdventBattle->toArray());
            UsrArtwork::insert($usrArtwork->toArray());
            UsrArtworkFragment::insert($usrArtworkFragment->toArray());
            UsrEmblem::insert($usrEmblem->toArray());
            UsrGachaUpper::insert($usrGachaUpper->toArray());
            UsrItem::insert($usrItems);
            UsrMessage::insert($usrMessage->toArray());
            UsrMissionDailyBonus::insert($usrMissionDailyBonus->toArray());
            UsrMissionEventDaily::insert($usrMissionEventDaily->toArray());
            UsrMissionEventDailyProgress::insert($dailyProgresses);
            UsrMissionEventDailyBonus::insert($usrMissionEventDailyBonus->toArray());
            UsrMissionEventDailyBonusProgress::insert($usrMissionEventDailyBonusProgress->toArray());
            UsrMissionEvent::insert($usrMissionEvents);
            UsrMissionEventProgress::insert($usrMissionEventProgresses);
            UsrMissionLimitedTerm::insert($usrMissionLimitedTerms);
            UsrMissionLimitedTermProgress::insert($usrMissionLimitedTermProgresses);
            UsrMissionNormal::insert($usrMissionNormals);
            UsrMissionRecentAddition::insert($usrMissionRecentAdditions);
            UsrOutpostEnhancement::insert($usrOutpostEnhancement->toArray());
            UsrShopItem::insert($usrShopItem->toArray());
            UsrShopPass::insert($usrShopPass->toArray());
            UsrStage::insert($usrStage->toArray());
            UsrStageSession::insert($usrStageSession->toArray());
            UsrStoreProduct::insert($usrStoreProducts);
            UsrUserBuyCount::insert($usrUserBuyCount->toArray());
            UsrUserLogin::insert($usrUserLogin->toArray());
        });
    }
}
