<?php

namespace Database\Seeders;

use App\Domain\Resource\Mst\Models\MstUnitExchange;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $mstUnitIds = collect(UnitConstant::INITIAL_UNIT_MST_UNIT_IDS);
        // $mstUnitIds->push('5', '6');

        // $data = [
        //     OprMasterReleaseControl::class => [
        //         [
        //             'id' => '1',
        //             'release_key' => 1,
        //             'git_revision' => 'test1',
        //             'release_at' => "2023-12-30 00:00:00",
        //             'release_description' => 'test1',
        //             'client_data_hash' => 'test1',
        //             'client_opr_data_hash' => 'test1',
        //             'created_at' => '2023-12-01 00:00:00'
        //         ],
        //     ],
        //     MstUnit::class => [
        //         ...collect($mstUnitIds)->map(function ($id) {
        //             return [
        //                 'id' => $id,
        //                 'fragment_mst_item_id' => $id,
        //                 'role_type' => 'Attack',
        //                 'unit_type' => 'Monster',
        //                 'unit_label' => 'DropR',
        //                 'attack_range' => 'Short',
        //                 'image_id' => 1,
        //                 'asset_key' => '1',
        //                 'rarity' => 'N',
        //                 'sort_order' => 1,
        //                 'summon_cost' => 1,
        //                 'summon_cool_time' => 1,
        //                 'min_hp' => 1,
        //                 'max_hp' => 1,
        //                 'damage_knock_back_count' => 1,
        //                 'move_speed' => 1,
        //                 'well_distance' => 1,
        //                 'min_attack_power' => 1,
        //                 'max_attack_power' => 1,
        //                 'attack_combo_cycle' => 1,
        //                 'normal_attack_id' => '1',
        //                 'special_attack_id' => '1',
        //                 'bounding_range_front' => 1,
        //                 'bounding_range_back' => 1,
        //                 'series_asset_key' => '1',
        //                 'avatar_icon_path' => '1',
        //                 'release_key' => 1,
        //             ];
        //         })->toArray(),
        //     ],
        //     MstUnitGradeUp::class => [
        //         ['id' => '1', 'grade_level' => 0, 'unit_label' => 'DropR', 'require_amount' => 1,],
        //         ['id' => '2', 'grade_level' => 1, 'unit_label' => 'DropR', 'require_amount' => 1,],
        //         ['id' => '3', 'grade_level' => 2, 'unit_label' => 'DropR', 'require_amount' => 1,],
        //         ['id' => '4', 'grade_level' => 3, 'unit_label' => 'DropR', 'require_amount' => 1,],
        //         ['id' => '5', 'grade_level' => 4, 'unit_label' => 'DropR', 'require_amount' => 1,],
        //     ],
        //     MstUnitLevelUp::class => [
        //         ['id' => '1', 'unit_label' => 'DropR', 'level' => 1, 'required_coin' => 0,],
        //         ['id' => '2', 'unit_label' => 'DropR', 'level' => 2, 'required_coin' => 100,],
        //         ['id' => '3', 'unit_label' => 'DropR', 'level' => 3, 'required_coin' => 200,],
        //         ['id' => '4', 'unit_label' => 'DropR', 'level' => 4, 'required_coin' => 300,],
        //         ['id' => '5', 'unit_label' => 'DropR', 'level' => 5, 'required_coin' => 400,],
        //         ['id' => '6', 'unit_label' => 'DropR', 'level' => 6, 'required_coin' => 500,],
        //         ['id' => '7', 'unit_label' => 'DropR', 'level' => 7, 'required_coin' => 600,],
        //         ['id' => '8', 'unit_label' => 'DropR', 'level' => 8, 'required_coin' => 700,],
        //         ['id' => '9', 'unit_label' => 'DropR', 'level' => 9, 'required_coin' => 800,],
        //         ['id' => '10', 'unit_label' => 'DropR', 'level' => 10, 'required_coin' => 900,],
        //     ],
        //     MstUnitRankUp::class => [
        //         ['id' => '1', 'unit_label' => 'DropR', 'rank' => 1, 'amount' => 1, 'require_level' => 1,],
        //         ['id' => '2', 'unit_label' => 'DropR', 'rank' => 2, 'amount' => 1, 'require_level' => 3,],
        //     ],
        //     MstUnitExchange::class => [
        //         ['id' => '1', 'mst_unit_id' => '1', 'required_mst_item_id' => '1', 'required_amount' => 1, 'required_diamond_amount' => 1,],
        //         ['id' => '2', 'mst_unit_id' => '2', 'required_mst_item_id' => '2', 'required_amount' => 1, 'required_diamond_amount' => 1,],
        //         ['id' => '3', 'mst_unit_id' => '3', 'required_mst_item_id' => '3', 'required_amount' => 1, 'required_diamond_amount' => 1,],
        //         ['id' => '4', 'mst_unit_id' => '4', 'required_mst_item_id' => '4', 'required_amount' => 1, 'required_diamond_amount' => 1,],
        //         ['id' => '5', 'mst_unit_id' => '5', 'required_mst_item_id' => '1', 'required_amount' => 1, 'required_diamond_amount' => 1,],
        //         ['id' => '6', 'mst_unit_id' => '6', 'required_mst_item_id' => '2', 'required_amount' => 1, 'required_diamond_amount' => 1,],
        //     ],
        //     MstItem::class => [
        //         ...collect($mstUnitIds)->map(function ($id) {
        //             return ['id' => $id, ];
        //         })->toArray(),
        //     ],
        //     MstStage::class => [
        //         ['id' => '1', 'prev_mst_stage_id' => null, 'mst_quest_id' => '1', 'sort_order' => 1],
        //     ],
        //     MstQuest::class => [
        //         ['id' => '1', 'start_date' => '2020-01-01 00:00:00', 'end_date' => '2038-01-09 03:14:07',],
        //     ],
        //     MstShopItem::class => [
        //         ['id' => '1', 'start_date' => '2020-01-01 00:00:00', 'end_date' => '2038-01-09 03:14:07',],
        //     ],
        //     MstUserLevel::class => [
        //         ['id' => '1', 'level' => 1, 'stamina' => 10, 'exp' => 100],
        //         ['id' => '2', 'level' => 2, 'stamina' => 11, 'exp' => 200],
        //         ['id' => '3', 'level' => 3, 'stamina' => 12, 'exp' => 300],
        //         ['id' => '4', 'level' => 4, 'stamina' => 13, 'exp' => 400],
        //         ['id' => '5', 'level' => 5, 'stamina' => 14, 'exp' => 500],
        //         ['id' => '6', 'level' => 6, 'stamina' => 15, 'exp' => 600],
        //         ['id' => '7', 'level' => 7, 'stamina' => 16, 'exp' => 700],
        //         ['id' => '8', 'level' => 8, 'stamina' => 17, 'exp' => 800],
        //         ['id' => '9', 'level' => 9, 'stamina' => 18, 'exp' => 900],
        //         ['id' => '10', 'level' => 10, 'stamina' => 19, 'exp' => 1000],
        //     ],
        //     MstUserLevelBonus::class => [
        //         ['id' => '1', 'level' => 1, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '2', 'level' => 2, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '3', 'level' => 3, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '4', 'level' => 4, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '5', 'level' => 5, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '6', 'level' => 6, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '7', 'level' => 7, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '8', 'level' => 8, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '9', 'level' => 9, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //         ['id' => '10', 'level' => 10, 'mst_user_level_bonus_group_id' => '1', 'release_key' => 1],
        //     ],
        //     MstUserLevelBonusGroup::class => [
        //         ['id' => '1', 'mst_user_level_bonus_group_id' => '1', 'resource_type' => RewardType::FREE_DIAMOND->value, 'resource_id' => '1', 'resource_amount' => 1, 'release_key' => 1],
        //     ],

        //     // IdleIncentive
        //     MstIdleIncentive::class => [
        //         [
        //             'id' => '1', 'initial_reward_receive_minutes' => 0, 'max_idle_hours' => 100,
        //             'max_daily_diamond_quick_receive_amount' => 3, 'required_quick_receive_diamond_amount' => 15,
        //         ],
        //     ],
        //     MstIdleIncentiveReward::class => [
        //         ['id' => '1', 'mst_stage_id' => '1', 'base_coin_amount' => 10, 'base_exp_amount' => 20],
        //     ],
        //     MstIdleIncentiveItem::class => [
        //         ['id' => '1', 'mst_idle_incentive_item_group_id' => '1', 'mst_item_id' => '1', 'base_amount' => 30],
        //     ],

        //     // Shop
        //     CurrencyOprProduct::class => [
        //         ['id' => 'pack_160_1_framework', 'mst_store_product_id' => 'pack_160_1_framework',
        //         'paid_amount' => 100,
        //         'start_date' => '2020-01-01 00:00:00', 'end_date' => '2038-01-09 03:14:07',],
        //     ],
        //     MstStoreProduct::class => [
        //         ['id' => 'pack_160_1_framework', 'product_id_ios' => "ios_pack_160_1_framework", 'product_id_android' => "android_pack_160_1_framework",],
        //     ],
        //     MstPack::class => [
        //         ['id' => 'mstPackId1', 'product_sub_id' => 'pack_160_1_framework', 'sale_condition' => null, 'cost_type' => 'cash',],
        //     ],
        //     MstConfig::class => [
        //         ['id' => '1', 'key' => MstConfigConstant::MAX_DAILY_BUY_STAMINA_AD_COUNT, 'value' => '3'],
        //         ['id' => '2', 'key' => MstConfigConstant::DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES, 'value' => '60'],
        //         ['id' => '3', 'key' => MstConfigConstant::BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA, 'value' => '50'],
        //         ['id' => '4', 'key' => MstConfigConstant::BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA, 'value' => '100'],
        //         ['id' => '5', 'key' => MstConfigConstant::BUY_STAMINA_DIAMOND_AMOUNT, 'value' => '100'],
        //         ['id' => '6', 'key' => MstConfigConstant::USER_NAME_CHANGE_INTERVAL_HOURS, 'value' => '24'],
        //         ['id' => '7', 'key' => MstConfigConstant::STAGE_CONTINUE_DIAMOND_AMOUNT, 'value' => '100'],
        //     ],
        // ];

        // foreach ($data as $class => $rows) {
        //     if (class_exists($class) === false) {
        //         continue;
        //     }
        //     foreach ($rows as $row) {
        //         $model = $class::find($row['id']);

        //         if (is_null($model)) {
        //             $class::factory()->create($row);
        //         } else {
        //             $model->update($row);
        //         }
        //     }
        // }
    }
}
