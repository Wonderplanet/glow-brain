<?php

namespace Feature\Domain\Encyclopedia\Services;

use App\Domain\Encyclopedia\Services\EncyclopediaEffectService;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use Tests\TestCase;

class EncyclopediaEffectServiceTest extends TestCase
{
    private EncyclopediaEffectService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(EncyclopediaEffectService::class);
    }

    public function testGetEncyclopediaEffectData_取得確認() {

        // Setup
        MstUnitEncyclopediaReward::factory()->createMany([
            ['id' => '1', 'unit_encyclopedia_rank' => 1,],
            ['id' => '2', 'unit_encyclopedia_rank' => 2,],
            ['id' => '3', 'unit_encyclopedia_rank' => 3,],
            ['id' => '4', 'unit_encyclopedia_rank' => 4,],
            ['id' => '5', 'unit_encyclopedia_rank' => 5,],
        ]);
        MstUnitEncyclopediaEffect::factory()->createMany([
            ['id' => 'effect1', 'mst_unit_encyclopedia_reward_id' => '1', 'effect_type' => 'Hp', 'value' => 10,],
            ['id' => 'effect2', 'mst_unit_encyclopedia_reward_id' => '1', 'effect_type' => 'AttackPower', 'value' => 11,],
            ['id' => 'effect3', 'mst_unit_encyclopedia_reward_id' => '2', 'effect_type' => 'Heal', 'value' => 20,],
            ['id' => 'effect4', 'mst_unit_encyclopedia_reward_id' => '2', 'effect_type' => 'Hp', 'value' => 21,],
            ['id' => 'effect5', 'mst_unit_encyclopedia_reward_id' => '3', 'effect_type' => 'AttackPower', 'value' => 30,],
            ['id' => 'effect6', 'mst_unit_encyclopedia_reward_id' => '3', 'effect_type' => 'Heal', 'value' => 31,],
            ['id' => 'effect7', 'mst_unit_encyclopedia_reward_id' => '4', 'effect_type' => 'Hp', 'value' => 40,],
            ['id' => 'effect8', 'mst_unit_encyclopedia_reward_id' => '4', 'effect_type' => 'AttackPower', 'value' => 41,],
            ['id' => 'effect9', 'mst_unit_encyclopedia_reward_id' => '5', 'effect_type' => 'Heal', 'value' => 50,],
            ['id' => 'effect10', 'mst_unit_encyclopedia_reward_id' => '5', 'effect_type' => 'Hp', 'value' => 51,],
        ]);

        // Exercise
        $encyclopediaEffectData = $this->service->getEncyclopediaEffectDataByIds(
            collect(['effect1', 'effect2', 'effect3', 'effect4', 'effect5', 'effect6'])
        );

        // Verify
        $this->assertEquals(31, $encyclopediaEffectData->getHpEffectPercentage());
        $this->assertEquals(41, $encyclopediaEffectData->getAttackPowerEffectPercentage());
        $this->assertEquals(51, $encyclopediaEffectData->getHealEffectPercentage());
    }
}
