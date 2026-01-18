<?php

namespace Feature\Domain\Gacha\Services;

use App\Domain\Gacha\Entities\GachaPrize;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Services\GachaTutorialService;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Unit\Enums\UnitLabel;
use Tests\TestCase;

class GachaTutorialServiceTest extends TestCase
{
    private GachaTutorialService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(GachaTutorialService::class);
    }

    public function test_draw_正常実行(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();
        $now = $this->fixTime();

        // mst
        $oprGacha = OprGacha::factory()->create([
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
        ])->toEntity();
        // 未使用のデータも用意。チュートリアルのガシャデータを取得することを確認するため
        OprGacha::factory()->createMany([
            ['gacha_type' => GachaType::NORMAL],
            ['gacha_type' => GachaType::PREMIUM],
        ]);
        OprGachaPrize::factory()->createMany([
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'weight' => 1,
                'pickup' => 1,
            ],
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_2',
                'resource_amount' => 1,
                'weight' => 1,
                'pickup' => 0,
            ],
            [
                'group_id' => 'prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_3',
                'resource_amount' => 1,
                'weight' => 98,
                'pickup' => 0,
            ],
            [
                'group_id' => 'fixed_prize_group_1',
                'resource_type' => RewardType::UNIT,
                'resource_id' => 'unit_1',
                'resource_amount' => 1,
                'weight' => 50,
                'pickup' => 0,
            ],
            [
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

        // Exercise
        $gachaResultData = $this->service->draw(
            $usrUserId,
            $now,
            $oprGacha,
            10,
            CostType::DIAMOND,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify

        // return確認
        $result = $gachaResultData->getResult();
        $this->assertCount(10, $result);
        $this->assertInstanceOf(GachaPrize::class, $result[0]);

        // DB確認

        $usrGacha = UsrGacha::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrGacha);
        $this->assertEquals(10, $usrGacha->getCount());
        $this->assertEquals($now->toDateTimeString(), $usrGacha->getPlayedAt());
    }
}
