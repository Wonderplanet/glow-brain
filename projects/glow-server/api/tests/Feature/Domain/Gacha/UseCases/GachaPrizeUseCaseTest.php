<?php

namespace Tests\Feature\Domain\Gacha\UseCases;

use App\Domain\Common\Enums\Language;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\UseCases\GachaPrizeUseCase;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprStepupGacha;
use App\Domain\Resource\Mst\Models\OprStepupGachaStep;
use Tests\TestCase;

class GachaPrizeUseCaseTest extends TestCase
{
    private GachaPrizeUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(GachaPrizeUseCase::class);
    }

    public function testExec_通常ガシャの賞品情報取得()
    {
        // Setup
        $this->createUsrUser();

        $oprGachaId = 'normal_gacha_1';
        $prizeGroupId = 'prize_group_1';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::NORMAL->value,
            'prize_group_id' => $prizeGroupId,
        ]);

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        // ユニットマスタと景品設定を作成
        $mstUnits = MstUnit::factory()->createMany([
            ['rarity' => RarityType::N->value],
            ['rarity' => RarityType::R->value],
            ['rarity' => RarityType::SR->value],
        ]);

        foreach ($mstUnits as $mstUnit) {
            OprGachaPrize::factory()->create([
                'group_id' => $prizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->id,
                'weight' => 100,
            ]);
        }

        // Exercise
        $resultData = $this->useCase->exec($oprGachaId);

        // Verify
        $this->assertNotNull($resultData->gachaProbabilityData);
        $this->assertTrue($resultData->stepupGachaPrizes->isEmpty());
    }

    public function testExec_ステップアップガシャの賞品情報取得()
    {
        // Setup
        $this->createUsrUser();

        $oprGachaId = 'stepup_gacha_1';
        $prizeGroupId = 'prize_group_1';
        $maxStepNumber = 5;

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
        ]);

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => 3,
        ]);

        // ユニットマスタと景品設定を作成
        $mstUnits = MstUnit::factory()->createMany([
            ['rarity' => RarityType::R->value],
            ['rarity' => RarityType::SR->value],
            ['rarity' => RarityType::SSR->value],
            ['rarity' => RarityType::UR->value],
        ]);

        foreach ($mstUnits as $mstUnit) {
            OprGachaPrize::factory()->create([
                'group_id' => $prizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->id,
                'weight' => 100,
            ]);
        }

        // ステップ設定を作成
        for ($i = 1; $i <= $maxStepNumber; $i++) {
            $fixedPrizeGroupId = $i >= 3 ? "step_{$i}_fixed_prize_group" : null;

            OprStepupGachaStep::factory()->create([
                'opr_gacha_id' => $oprGachaId,
                'step_number' => $i,
                'draw_count' => 10,
                'fixed_prize_count' => $i >= 3 ? 1 : 0,
                'prize_group_id' => $prizeGroupId,
                'fixed_prize_group_id' => $fixedPrizeGroupId,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 1500,
            ]);

            // ステップ3以降は確定枠用の景品グループを作成
            if ($i >= 3 && $fixedPrizeGroupId) {
                foreach ($mstUnits->filter(fn($u) => $u->rarity >= RarityType::SR->value) as $mstUnit) {
                    OprGachaPrize::factory()->create([
                        'group_id' => $fixedPrizeGroupId,
                        'resource_type' => RewardType::UNIT,
                        'resource_id' => $mstUnit->id,
                        'weight' => 100,
                    ]);
                }
            }
        }

        // Exercise
        $resultData = $this->useCase->exec($oprGachaId);

        // Verify
        $this->assertNotNull($resultData->gachaProbabilityData);
        $this->assertNotNull($resultData->stepupGachaPrizes);
        $this->assertCount($maxStepNumber, $resultData->stepupGachaPrizes);

        // 各ステップの情報を検証
        foreach ($resultData->stepupGachaPrizes as $index => $stepPrize) {
            $stepNumber = $index + 1;
            $this->assertEquals($stepNumber, $stepPrize->getStepNumber());
            $this->assertEquals(10, $stepPrize->getDrawCount());
            $this->assertEquals($stepNumber >= 3 ? 1 : 0, $stepPrize->getFixedPrizeCount());

            // 確定枠がある場合のみ、レアリティ確率と賞品グループが存在する
            if ($stepNumber >= 3) {
                $this->assertNotEmpty($stepPrize->getRarityProbabilities());
                $this->assertNotEmpty($stepPrize->getProbabilityGroups());
            }
        }
    }

    public function testExec_ステップごとに異なる景品グループ()
    {
        // Setup
        $this->createUsrUser();

        $oprGachaId = 'stepup_gacha_1';
        $basePrizeGroupId = 'base_prize_group';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $basePrizeGroupId,
        ]);

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 3,
            'max_loop_count' => 1,
        ]);

        // ベース景品グループ
        $baseUnits = MstUnit::factory()->createMany([
            ['rarity' => RarityType::N->value],
            ['rarity' => RarityType::R->value],
        ]);
        foreach ($baseUnits as $unit) {
            OprGachaPrize::factory()->create([
                'group_id' => $basePrizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $unit->id,
                'weight' => 100,
            ]);
        }

        // ステップ3専用の景品グループ（UR確定）
        $step3PrizeGroupId = 'step_3_prize_group';
        $urUnit = MstUnit::factory()->create(['rarity' => RarityType::UR->value]);
        OprGachaPrize::factory()->create([
            'group_id' => $step3PrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => $urUnit->id,
            'weight' => 100,
        ]);

        // ステップ設定
        OprStepupGachaStep::factory()->createMany([
            [
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 1,
                'draw_count' => 10,
                'fixed_prize_count' => 0,
                'prize_group_id' => $basePrizeGroupId,
                'fixed_prize_group_id' => null,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 1500,
            ],
            [
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 2,
                'draw_count' => 10,
                'fixed_prize_count' => 0,
                'prize_group_id' => $basePrizeGroupId,
                'fixed_prize_group_id' => null,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 1500,
            ],
            [
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 3,
                'draw_count' => 10,
                'fixed_prize_count' => 1,
                'prize_group_id' => $basePrizeGroupId,
                'fixed_prize_group_id' => $step3PrizeGroupId,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 1500,
            ],
        ]);

        // Exercise
        $resultData = $this->useCase->exec($oprGachaId);

        // Verify
        $this->assertCount(3, $resultData->stepupGachaPrizes);

        // ステップ3の確定枠確率を検証
        $step3Prize = $resultData->stepupGachaPrizes[2];
        $this->assertEquals(3, $step3Prize->getStepNumber());
        $this->assertEquals(1, $step3Prize->getFixedPrizeCount());

        // ステップ3の確率情報にUR確定の情報が含まれることを確認
        $this->assertNotEmpty($step3Prize->getRarityProbabilities());
        $this->assertNotEmpty($step3Prize->getProbabilityGroups());
    }

    public function testExec_ステップアップガシャ_確定枠なしステップ()
    {
        // Setup
        $this->createUsrUser();

        $oprGachaId = 'stepup_gacha_1';
        $prizeGroupId = 'prize_group_1';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
        ]);

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 2,
            'max_loop_count' => 1,
        ]);

        $mstUnit = MstUnit::factory()->create(['rarity' => RarityType::R->value]);
        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => $mstUnit->id,
            'weight' => 100,
        ]);

        // 確定枠なしのステップ設定
        OprStepupGachaStep::factory()->createMany([
            [
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 1,
                'draw_count' => 10,
                'fixed_prize_count' => 0,
                'prize_group_id' => $prizeGroupId,
                'fixed_prize_group_id' => null,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 1500,
            ],
            [
                'opr_gacha_id' => $oprGachaId,
                'step_number' => 2,
                'draw_count' => 10,
                'fixed_prize_count' => 0,
                'prize_group_id' => $prizeGroupId,
                'fixed_prize_group_id' => null,
                'cost_type' => CostType::DIAMOND->value,
                'cost_num' => 1500,
            ],
        ]);

        // Exercise
        $resultData = $this->useCase->exec($oprGachaId);

        // Verify
        $this->assertCount(2, $resultData->stepupGachaPrizes);

        foreach ($resultData->stepupGachaPrizes as $stepPrize) {
            $this->assertEquals(0, $stepPrize->getFixedPrizeCount());
        }
    }
}
