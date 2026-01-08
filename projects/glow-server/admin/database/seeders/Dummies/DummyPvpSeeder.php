<?php

namespace Database\Seeders\Dummies;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Models\GenericMstModel;
use App\Models\GenericSysModel;
use App\Models\GenericUsrModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DummyPvpSeeder extends Seeder
{
    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        $now = CarbonImmutable::now();
        $mstPvpId = 'test_pvp_1';
        $mstInGameId = 'test_in_game_1';
        $sysPvpSeasonId = 'test_pvp_season';
        $usrPvpId = 'test_usr_pvp_1';
        $usrUserId = 'test_user_1';

        $mstPvpModel = (new GenericMstModel())->setTableName('mst_pvps');
        $mstPvpI18nModel = (new GenericMstModel())->setTableName('mst_pvps_i18n');
        $mstPvpRankModel = (new GenericMstModel())->setTableName('mst_pvp_ranks');
        $sysPvpSeasonModel = (new GenericSysModel())->setTableName('sys_pvp_seasons');
        $usrPvpModel = (new GenericUsrModel())->setTableName('usr_pvps');

        $mstPvpModel->newQuery()->upsert(
            [
                [
                    'id' => $mstPvpId,
                    'release_key' => 1,
                    'ranking_min_pvp_rank_class' => 'Bronze',
                    'max_daily_challenge_count' => 0,
                    'max_daily_item_challenge_count' => 0,
                    'item_challenge_cost_amount' => 0,
                    'mst_in_game_id' => $mstInGameId,
                ],
            ],
            ['id'],
            [
                'release_key',
                'ranking_min_pvp_rank_class',
                'max_daily_challenge_count',
                'max_daily_item_challenge_count',
                'item_challenge_cost_amount',
                'mst_in_game_id',
            ]
        );
        $mstPvpI18nModel->newQuery()->upsert(
            [
                [
                    'id' => $mstPvpId . '_i18n',
                    'release_key' => 1,
                    'mst_pvp_id' => $mstPvpId,
                    'language' => 'ja',
                    'name' => 'テストPvP',
                    'description' => 'テスト用のPvPです。',
                ],
            ],
            ['id'],
            [
                'release_key',
                'mst_pvp_id',
                'language',
                'name',
                'description',
            ]
        );
        $mstPvpRankModel->newQuery()->upsert(
            [
                [
                    'id' => PvpRankClassType::BRONZE->value . '_1',
                    'rank_class_type' => PvpRankClassType::BRONZE->value,
                    'rank_class_level' => 1,
                    'required_lower_score' => 0,
                    'win_add_point' => 100,
                    'lose_sub_point' => 100,
                    'asset_key' => 'bronze_asset',
                ],
                [
                    'id' => PvpRankClassType::BRONZE->value . '_2',
                    'rank_class_type' => PvpRankClassType::BRONZE->value,
                    'rank_class_level' => 2,
                    'required_lower_score' => 500,
                    'win_add_point' => 150,
                    'lose_sub_point' => 150,
                    'asset_key' => 'bronze_asset',
                ],
                [
                    'id' => PvpRankClassType::BRONZE->value . '_3',
                    'rank_class_type' => PvpRankClassType::BRONZE->value,
                    'rank_class_level' => 3,
                    'required_lower_score' => 800,
                    'win_add_point' => 180,
                    'lose_sub_point' => 180,
                    'asset_key' => 'bronze_asset',
                ],
                [
                    'id' => PvpRankClassType::SILVER->value . '_1',
                    'rank_class_type' => PvpRankClassType::SILVER->value,
                    'rank_class_level' => 1,
                    'required_lower_score' => 1000,
                    'win_add_point' => 200,
                    'lose_sub_point' => 200,
                    'asset_key' => 'silver_asset',
                ],
                [
                    'id' => PvpRankClassType::SILVER->value . '_2',
                    'rank_class_type' => PvpRankClassType::SILVER->value,
                    'rank_class_level' => 2,
                    'required_lower_score' => 1500,
                    'win_add_point' => 220,
                    'lose_sub_point' => 220,
                    'asset_key' => 'silver_asset',
                ],
                [
                    'id' => PvpRankClassType::SILVER->value . '_3',
                    'rank_class_type' => PvpRankClassType::SILVER->value,
                    'rank_class_level' => 3,
                    'required_lower_score' => 1800,
                    'win_add_point' => 250,
                    'lose_sub_point' => 250,
                    'asset_key' => 'silver_asset',
                ],
                [
                    'id' => PvpRankClassType::GOLD->value . '_1',
                    'rank_class_type' => PvpRankClassType::GOLD->value,
                    'rank_class_level' => 1,
                    'required_lower_score' => 2000,
                    'win_add_point' => 300,
                    'lose_sub_point' => 300,
                    'asset_key' => 'gold_asset',
                ],
                [
                    'id' => PvpRankClassType::GOLD->value . '_2',
                    'rank_class_type' => PvpRankClassType::GOLD->value,
                    'rank_class_level' => 2,
                    'required_lower_score' => 2500,
                    'win_add_point' => 350,
                    'lose_sub_point' => 350,
                    'asset_key' => 'gold_asset',
                ],
                [
                    'id' => PvpRankClassType::GOLD->value . '_3',
                    'rank_class_type' => PvpRankClassType::GOLD->value,
                    'rank_class_level' => 3,
                    'required_lower_score' => 2800,
                    'win_add_point' => 380,
                    'lose_sub_point' => 380,
                    'asset_key' => 'gold_asset',
                ],
                [
                    'id' => PvpRankClassType::PLATINUM->value . '_1',
                    'rank_class_type' => PvpRankClassType::PLATINUM->value,
                    'rank_class_level' => 1,
                    'required_lower_score' => 3000,
                    'win_add_point' => 400,
                    'lose_sub_point' => 400,
                    'asset_key' => 'platinum_asset',
                ],
                [
                    'id' => PvpRankClassType::PLATINUM->value . '_2',
                    'rank_class_type' => PvpRankClassType::PLATINUM->value,
                    'rank_class_level' => 2,
                    'required_lower_score' => 3500,
                    'win_add_point' => 450,
                    'lose_sub_point' => 450,
                    'asset_key' => 'platinum_asset',
                ],
                [
                    'id' => PvpRankClassType::PLATINUM->value . '_3',
                    'rank_class_type' => PvpRankClassType::PLATINUM->value,
                    'rank_class_level' => 3,
                    'required_lower_score' => 4000,
                    'win_add_point' => 500,
                    'lose_sub_point' => 500,
                    'asset_key' => 'platinum_asset',
                ],
            ],
            ['id'],
            [
                'rank_class_type',
                'rank_class_level',
                'required_lower_score',
                'win_add_point',
                'lose_sub_point',
                'asset_key',
            ]
        );
        $sysPvpSeasonModel->newQuery()->upsert(
            [
                [
                    'id' => $sysPvpSeasonId,
                    'mst_pvp_id' => $mstPvpId,
                    'start_at' => $now->subYear()->toDateTimeString(),
                    'end_at' => $now->addYear()->toDateTimeString(),
                    'closed_at' => null,
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
            ['mst_pvp_id', 'start_at', 'end_at', 'closed_at', 'created_at', 'updated_at']
        );
        $usrPvpModel->newQuery()->upsert(
            [
                [
                    'id' => $usrPvpId,
                    'usr_user_id' => $usrUserId,
                    'sys_pvp_season_id' => $sysPvpSeasonId,
                    'score' => 0,
                    'pvp_rank_class_type' => 'Bronze',
                    'pvp_rank_class_level' => 1,
                    'ranking' => 100,
                    'is_season_reward_received' => 0,
                    'is_excluded_ranking' => 0,
                    'daily_remaining_challenge_count' => 0,
                    'daily_remaining_item_challenge_count' => 0,
                    'last_played_at' => null,
                    'selected_opponent_candidates' => null,
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
            [
                'usr_user_id',
                'sys_pvp_season_id',
                'score',
                'pvp_rank_class_type',
                'pvp_rank_class_level',
                'ranking',
                'is_season_reward_received',
                'is_excluded_ranking',
                'daily_remaining_challenge_count',
                'daily_remaining_item_challenge_count',
                'last_played_at',
                'selected_opponent_candidates',
                'created_at',
                'updated_at',
            ]
        );
    }
}
