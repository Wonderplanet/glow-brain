<?php

namespace Database\Seeders\Dummies;

use App\Constants\GachaType;
use App\Models\GenericMstModel;
use App\Models\GenericUsrModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * ステップアップガシャのダミーデータを生成する
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyStepUpGachaSeeder"
 */
class DummyStepUpGachaSeeder extends Seeder
{
    public function run(): void
    {
        $now = CarbonImmutable::now();
        $usrUserId = 'test_user_1';
        $oprGachaId = 'stepup_gacha_test_001';

        // マスターデータモデル
        $oprGachaModel = (new GenericMstModel())->setTableName('opr_gachas');
        $oprGachaI18nModel = (new GenericMstModel())->setTableName('opr_gachas_i18n');
        $oprStepUpGachaModel = (new GenericMstModel())->setTableName('opr_stepup_gachas');
        $oprStepUpGachaStepModel = (new GenericMstModel())->setTableName('opr_stepup_gacha_steps');
        $oprStepUpGachaStepRewardModel = (new GenericMstModel())->setTableName('opr_stepup_gacha_step_rewards');

        // ユーザーデータモデル
        $usrGachaModel = (new GenericUsrModel())->setTableName('usr_gachas');

        // ガシャマスター作成
        $oprGachaModel->newQuery()->upsert(
            [
                [
                    'id' => $oprGachaId,
                    'gacha_type' => GachaType::STEPUP->value,
                    'upper_group' => 'None',
                    'enable_ad_play' => false,
                    'enable_add_ad_play_upper' => false,
                    'ad_play_interval_time' => null,
                    'multi_draw_count' => 10,
                    'multi_fixed_prize_count' => 1,
                    'daily_play_limit_count' => null,
                    'total_play_limit_count' => null,
                    'daily_ad_limit_count' => null,
                    'total_ad_limit_count' => null,
                    'prize_group_id' => 'prize_group_001',
                    'fixed_prize_group_id' => null,
                    'appearance_condition' => 'Always',
                    'unlock_condition_type' => 'None',
                    'unlock_duration_hours' => null,
                    'start_at' => $now->subDays(10)->toDateTimeString(),
                    'end_at' => $now->addDays(30)->toDateTimeString(),
                    'display_mst_unit_id' => '',
                    'display_information_id' => 'dummy_info_001',
                    'display_gacha_caution_id' => '',
                    'gacha_priority' => 1,
                    'release_key' => 1,
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
        );

        // ガシャ多言語データ（テーブル名を確認してスキップまたは修正）
        $oprGachaI18nModel->newQuery()->upsert(
            [
                [
                    'id' => $oprGachaId . '_i18n',
                    'opr_gacha_id' => $oprGachaId,
                    'language' => 'ja',
                    'name' => 'テストステップアップガシャ',
                    'description' => 'ステップアップガシャのテストデータです',
                    'gacha_background_color' => '#FFD700',
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
        );

        // ステップアップガシャマスター
        $oprStepUpGachaModel->newQuery()->upsert(
            [
                [
                    'id' => $oprGachaId . '_stepup',
                    'release_key' => 1,
                    'opr_gacha_id' => $oprGachaId,
                    'max_step_number' => 5,
                    'max_loop_count' => 3,
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
        );

        // 4. ステップアップガシャの各ステップ
        $steps = [];
        for ($step = 1; $step <= 5; $step++) {
            $steps[] = [
                'id' => $oprGachaId . '_step_' . $step,
                'release_key' => 1,
                'opr_gacha_id' => $oprGachaId,
                'step_number' => $step,
                'cost_type' => 'PaidDiamond',
                'cost_id' => null,
                'cost_num' => 100 * $step, // ステップごとに値段が上がる
                'draw_count' => $step === 5 ? 11 : 10, // 最終ステップは11連
                'fixed_prize_count' => 1,
                'fixed_prize_rarity_threshold_type' => null,
                'prize_group_id' => 'prize_group_001',
                'fixed_prize_group_id' => null,
                'is_first_free' => false,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ];
        }
        $oprStepUpGachaStepModel->newQuery()->upsert($steps, ['id']);

        // ステップ報酬（おまけ）- テーブルが存在しない場合はスキップ
        $stepRewards = [
            // ステップ3でアイテムプレゼント（全周回）
            [
                'id' => $oprGachaId . '_reward_1',
                'release_key' => 1,
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 3,
                'loop_count_target' => null, // 全周回
                'resource_type' => 'Item',
                'resource_id' => 'item_001',
                'resource_amount' => 10,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            // ステップ5で有償ダイヤプレゼント（1周目のみ）
            [
                'id' => $oprGachaId . '_reward_2',
                'release_key' => 1,
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 5,
                'loop_count_target' => 0, // 1周目のみ（0-indexed）
                'resource_type' => 'FreeDiamond',
                'resource_id' => null,
                'resource_amount' => 500,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
            // ステップ5でダイヤプレゼント（2周目以降）
            [
                'id' => $oprGachaId . '_reward_3',
                'release_key' => 1,
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 5,
                'loop_count_target' => 1, // 2周目（0-indexed）
                'resource_type' => 'Coin',
                'resource_id' => null,
                'resource_amount' => 300,
                'created_at' => $now->toDateTimeString(),
                'updated_at' => $now->toDateTimeString(),
            ],
        ];
        $oprStepUpGachaStepRewardModel->newQuery()->upsert($stepRewards, ['id']);

        // 6. ユーザーのガシャ進捗データ
        $usrGachaModel->newQuery()->upsert(
            [
                [
                    'id' => 'usr_gacha_' . $oprGachaId . '_' . $usrUserId,
                    'usr_user_id' => $usrUserId,
                    'opr_gacha_id' => $oprGachaId,
                    'ad_played_at' => null,
                    'played_at' => $now->subHours(2)->toDateTimeString(),
                    'ad_count' => 0,
                    'ad_daily_count' => 0,
                    'count' => 12,
                    'daily_count' => 5,
                    'expires_at' => null,
                    'current_step_number' => 3,
                    'loop_count' => 2,
                    'created_at' => $now->subDays(5)->toDateTimeString(),
                    'updated_at' => $now->subHours(2)->toDateTimeString(),
                ],
            ],
            ['id'],
        );
    }
}
