<?php

declare(strict_types=1);

namespace Feature\Domain\Tutorial\UseCases;

use App\Domain\Gacha\Entities\GachaPrize;
use App\Domain\Gacha\Entities\GachaResultData;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Gacha\Delegators\GachaDelegator;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Resource\Entities\Rewards\GachaReward;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Tutorial\Models\UsrTutorialGacha;
use App\Domain\Tutorial\UseCases\TutorialGachaDrawUseCase;
use App\Domain\Unit\Enums\UnitLabel;
use Tests\TestCase;

class TutorialGachaDrawUseCaseTest extends TestCase
{
    public function test_exec_チュートリアルガシャを引く_1回目()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getUsrUserId();
        $now = $this->fixTime();
        $currentUser = new CurrentUser($usrUserId);

        // mst
        OprGacha::factory()->create([
            'id' => 'tutorial_gacha_1',
            'gacha_type' => GachaType::TUTORIAL,
            'upper_group' => 'None',
            'enable_ad_play' => 0,
            'enable_add_ad_play_upper' => 0,
            'ad_play_interval_time' => null,
            'multi_draw_count' => 10,
            'multi_fixed_prize_count' => 1,
            'daily_play_limit_count' => null,
            'total_play_limit_count' => null,
            'daily_ad_limit_count' => null,
            'total_ad_limit_count' => null,
            'prize_group_id' => 'prize_group_1',
            'fixed_prize_group_id' => 'fixed_prize_group_1',
            'appearance_condition' => 'Always',
            'start_at' => '2020-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        OprGachaPrize::factory()->createMany([
            [
                'id' => 'prize1',
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'weight' => 1,
                'pickup' => 1,
            ],
            [
                'id' => 'prize2',
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_2',
                'resource_amount' => 1,
                'weight' => 1,
                'pickup' => 0,
            ],
            [
                'id' => 'prize3',
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_3',
                'resource_amount' => 1,
                'weight' => 98,
                'pickup' => 0,
            ],
            [
                'id' => 'prize4',
                'group_id' => 'fixed_prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'weight' => 50,
                'pickup' => 0,
            ],
            [
                'id' => 'prize5',
                'group_id' => 'fixed_prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_2',
                'resource_amount' => 1,
                'weight' => 50,
                'pickup' => 0,
            ],
        ]);
        MstUnit::factory()->createMany([
            ['id' => 'unit_1', 'unit_label' => UnitLabel::DROP_UR, 'fragment_mst_item_id' => 'fragment_unit_1'],
            ['id' => 'unit_2', 'unit_label' => UnitLabel::DROP_UR, 'fragment_mst_item_id' => 'fragment_unit_2'],
            ['id' => 'unit_3', 'unit_label' => UnitLabel::DROP_SR, 'fragment_mst_item_id' => 'fragment_unit_3'],
        ]);
        MstUnitFragmentConvert::factory()->createMany([
            ['unit_label' => UnitLabel::DROP_SR, 'convert_amount' => 10],
            ['unit_label' => UnitLabel::DROP_UR, 'convert_amount' => 20],
        ]);

        // mock
        $gachaDelegator = $this->mock(GachaDelegator::class, function ($mock) {
            $mock->shouldReceive('drawTutorial')->once()->andReturn(new GachaResultData(
                'tutorial_gacha_1',
                collect([
                    // unit_1 × 1, unit_2 × 2, unit_3 × 7
                    new GachaPrize('prize3', 'prize_group_1', RewardType::UNIT, 'unit_3', 1, 98, false, 'R'),
                    new GachaPrize('prize3', 'prize_group_1', RewardType::UNIT, 'unit_3', 1, 98, false, 'R'),
                    new GachaPrize('prize3', 'prize_group_1', RewardType::UNIT, 'unit_3', 1, 98, false, 'R'),
                    new GachaPrize('prize3', 'prize_group_1', RewardType::UNIT, 'unit_3', 1, 98, false, 'R'),
                    new GachaPrize('prize2', 'prize_group_1', RewardType::UNIT, 'unit_2', 1, 1, false, 'R'),
                    new GachaPrize('prize3', 'prize_group_1', RewardType::UNIT, 'unit_3', 1, 98, false, 'R'),
                    new GachaPrize('prize2', 'prize_group_1', RewardType::UNIT, 'unit_2', 1, 1, false, 'R'),
                    new GachaPrize('prize3', 'prize_group_1', RewardType::UNIT, 'unit_3', 1, 98, false, 'R'),
                    new GachaPrize('prize3', 'prize_group_1', RewardType::UNIT, 'unit_3', 1, 98, false, 'R'),
                    new GachaPrize('prize1', 'prize_group_1', RewardType::UNIT, 'unit_1', 1, 1, false, 'R'),
                ]),
                ['Regular', 'Regular', 'Regular', 'Regular', 'Regular', 'Regular', 'Regular', 'Regular', 'Regular', 'Regular']
            ));
            $mock->shouldReceive('makeGachaRewardByGachaBoxes')->once()->andReturn(collect([
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'tutorial_gacha_1', 0),
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'tutorial_gacha_1', 1),
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'tutorial_gacha_1', 2),
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'tutorial_gacha_1', 3),
                    new GachaReward(RewardType::UNIT->value, 'unit_2', 1, 'tutorial_gacha_1', 4),
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'tutorial_gacha_1', 5),
                    new GachaReward(RewardType::UNIT->value, 'unit_2', 1, 'tutorial_gacha_1', 6),
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'tutorial_gacha_1', 7),
                    new GachaReward(RewardType::UNIT->value, 'unit_3', 1, 'tutorial_gacha_1', 8),
                    new GachaReward(RewardType::UNIT->value, 'unit_1', 1, 'tutorial_gacha_1', 9),
                ]
            ));
        });
        app()->instance(GachaDelegator::class, $gachaDelegator);
        $useCase = app(TutorialGachaDrawUseCase::class);

        // Exercise
        $resultData = $useCase->exec(
            $currentUser,
        );

        // Verify
        $usrTutorialGacha = UsrTutorialGacha::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrTutorialGacha);
        // チュートリアルガシャの一時保存には、排出結果そのままで保存できている
        $json = json_decode($usrTutorialGacha->gacha_result_json, true);
        $actual = collect($json['result']);
        $this->assertCount(10, $actual);
        $this->assertEquals(RewardType::UNIT->value, $actual->map(fn ($item) => $item['resource_type'])->first());
        $this->assertEqualsCanonicalizing(
            [
                'unit_3', 'unit_3', 'unit_3', 'unit_3', 'unit_3', 'unit_3', 'unit_3',
                'unit_2', 'unit_2',
                'unit_1',
            ],
            $actual->map(fn ($item) => $item['resource_id'])->toArray(),
        );
        $this->assertEqualsCanonicalizing(
            [
                'prize3', 'prize3', 'prize3', 'prize3', 'prize3', 'prize3', 'prize3',
                'prize2', 'prize2',
                'prize1',
            ],
            $actual->map(fn ($item) => $item['id'])->toArray(),
        );
        $this->assertEquals('prize_group_1', $actual->map(fn ($item) => $item['group_id'])->first());
        $this->assertEqualsCanonicalizing(
            [98, 98, 98, 1, 98, 1, 98, 98, 98, 1],
            $actual->map(fn ($item) => $item['weight'])->toArray(),
        );
        $this->assertEquals(0, $actual->map(fn ($item) => $item['pickup'])->first());
        $this->assertEquals('R', $actual->map(fn ($item) => $item['rarity'])->first());
        $this->assertEquals('Regular', array_unique($json['prize_types'])[0]);

        // レスポンスでは変換が考慮されている
        $actuals = $resultData->gachaRewards;
        // 配布ユニット
        $gachaRewards = $actuals->groupBy->getType()->get(RewardType::UNIT->value, collect());
        $this->assertCount(3, $gachaRewards);
        $this->assertEqualsCanonicalizing(
            ['unit_1', 'unit_2', 'unit_3'],
            $gachaRewards->map->getResourceId()->toArray(),
        );
        $this->assertEqualsCanonicalizing(
            // index順にユニットを獲得しているので、初獲得時のインデックスを確認する
            [0, 4, 9],
            $gachaRewards->map->getSortOrder()->toArray(),
        );
        // 重複ユニットがキャラのかけらアイテムに変換されている
        $gachaRewards = $actuals->groupBy->getType()->get(RewardType::ITEM->value, collect());
        $this->assertCount(7, $gachaRewards);
        $this->assertEqualsCanonicalizing(
            [
                'fragment_unit_2',
                'fragment_unit_3', 'fragment_unit_3', 'fragment_unit_3',
                'fragment_unit_3', 'fragment_unit_3', 'fragment_unit_3',
            ],
            $gachaRewards->map->getResourceId()->toArray(),
        );
    }
}
