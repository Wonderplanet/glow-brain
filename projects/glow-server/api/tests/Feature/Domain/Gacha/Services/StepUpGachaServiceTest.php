<?php

namespace Feature\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Services\StepUpGachaService;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprStepUpGacha;
use App\Domain\Resource\Mst\Models\OprStepUpGachaStep;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StepUpGachaServiceTest extends TestCase
{
    private StepUpGachaService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(StepUpGachaService::class);
    }

    public function testFilterPrizesByRarity_レアリティ閾値でフィルタリング()
    {
        // Setup
        $this->createUsrUser();

        $prizeGroupId = 'prize_group';
        $rarityThreshold = RarityType::SR;

        // ユニットマスタ作成
        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit_n', 'rarity' => RarityType::N->value],
            ['id' => 'unit_r', 'rarity' => RarityType::R->value],
            ['id' => 'unit_sr', 'rarity' => RarityType::SR->value],
            ['id' => 'unit_ssr', 'rarity' => RarityType::SSR->value],
            ['id' => 'unit_ur', 'rarity' => RarityType::UR->value],
        ]);

        // 景品設定
        foreach ($mstUnits as $mstUnit) {
            OprGachaPrize::factory()->create([
                'group_id' => $prizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->id,
                'resource_amount' => 1,
                'weight' => 100,
            ]);
        }

        $prizes = OprGachaPrize::where('group_id', $prizeGroupId)
            ->get()
            ->map(fn($p) => $p->toEntity());

        // Exercise - Reflectionを使ってprivateメソッドにアクセス
        $reflection = new \ReflectionClass(StepUpGachaService::class);
        $method = $reflection->getMethod('filterPrizesByRarity');
        $method->setAccessible(true);
        $filtered = $method->invoke($this->service, $prizes, $rarityThreshold);

        // Verify
        // SR以上のみが含まれる
        $this->assertCount(3, $filtered);
        $resourceIds = $filtered->map(fn($p) => $p->getResourceId())->toArray();
        $this->assertContains('unit_sr', $resourceIds);
        $this->assertContains('unit_ssr', $resourceIds);
        $this->assertContains('unit_ur', $resourceIds);
        $this->assertNotContains('unit_n', $resourceIds);
        $this->assertNotContains('unit_r', $resourceIds);
    }

    public function testInitializeAndValidate_初回ガチャ()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        OprStepUpGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => null,
            'loop_count' => null,
        ]);

        // Exercise
        $state = $this->service->initializeAndValidate($usrGacha, $oprGachaId, 10);

        // Verify
        $this->assertEquals(1, $state->getCurrentStepNumber());
        $this->assertEquals(0, $state->getLoopCount());
        $this->assertEquals(1, $state->getStepUpGachaStep()->getStepNumber());
    }

    public function testInitializeAndValidate_クライアントとサーバーのステップ不一致でエラー()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
        ]);

        OprStepUpGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 2,
            'draw_count' => 10,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 2,
            'loop_count' => 0,
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_STEPUP_STEP_MISMATCH);

        $this->service->initializeAndValidate($usrGacha, $oprGachaId, 10, 1); // クライアント側は1だと主張
    }

    public function testInitializeAndValidate_最大周回数超過でエラー()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';
        $maxLoopCount = 3;

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => $maxLoopCount,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 1,
            'loop_count' => $maxLoopCount,
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_STEPUP_MAX_LOOP_COUNT_EXCEEDED);

        $this->service->initializeAndValidate($usrGacha, $oprGachaId, 10);
    }

    public function testInitializeAndValidate_引く回数の不一致でエラー()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ]);

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
        ]);

        OprStepUpGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 1,
            'loop_count' => 0,
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_NOT_EXPECTED_PLAY_NUM);

        $this->service->initializeAndValidate($usrGacha, $oprGachaId, 5); // 10回引くべきなのに5回
    }

    public function testProgressStep_次のステップへ進行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 2,
            'loop_count' => 0,
        ]);

        $stepUpGacha = OprStepUpGacha::where('opr_gacha_id', $oprGachaId)->firstOrFail()->toEntity();

        // Exercise
        $this->service->progressStep($usrGacha, $stepUpGacha);

        // Verify
        $this->assertEquals(3, $usrGacha->getCurrentStepNumber());
        $this->assertEquals(0, $usrGacha->getLoopCount());
    }

    public function testProgressStep_最終ステップで周回数が増える()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';
        $maxStepNumber = 5;

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => 3,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => $maxStepNumber,
            'loop_count' => 1,
        ]);

        $stepUpGacha = OprStepUpGacha::where('opr_gacha_id', $oprGachaId)->firstOrFail()->toEntity();

        // Exercise
        $this->service->progressStep($usrGacha, $stepUpGacha);

        // Verify
        $this->assertEquals(1, $usrGacha->getCurrentStepNumber());
        $this->assertEquals(2, $usrGacha->getLoopCount());
    }

    public function testProgressStep_最大周回数到達でエラー()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';
        $maxStepNumber = 5;
        $maxLoopCount = 3;

        OprStepUpGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => $maxLoopCount,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => $maxStepNumber,
            'loop_count' => $maxLoopCount,
        ]);

        $stepUpGacha = OprStepUpGacha::where('opr_gacha_id', $oprGachaId)->firstOrFail()->toEntity();

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_STEPUP_MAX_LOOP_COUNT_EXCEEDED);

        $this->service->progressStep($usrGacha, $stepUpGacha);
    }

    #[DataProvider('params_testValidateCost')]
    public function testValidateCost(
        CostType $costType,
        ?string $costId,
        int $costNum,
        int $loopCount,
        bool $isFirstFree,
        CostType $stepCostType,
        ?string $stepCostId,
        int $stepCostNum,
        bool $expectError
    ) {
        // Setup
        $oprGachaId = 'stepup_gacha_001';

        OprStepUpGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'is_first_free' => $isFirstFree,
            'cost_type' => $stepCostType->value,
            'cost_id' => $stepCostId,
            'cost_num' => $stepCostNum,
        ]);

        $oprStepUpGachaStep = OprStepUpGachaStep::where('opr_gacha_id', $oprGachaId)
            ->where('step_number', 1)
            ->firstOrFail()
            ->toEntity();

        // Exercise & Verify
        if ($expectError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::GACHA_UNJUST_COSTS);
        }

        $this->service->validateCost($oprStepUpGachaStep, $costType, $costId, $costNum, $loopCount);

        if (!$expectError) {
            $this->assertTrue(true);
        }
    }

    public static function params_testValidateCost()
    {
        return [
            '初回無料_正常' => [
                'costType' => CostType::FREE,
                'costId' => null,
                'costNum' => 0,
                'loopCount' => 0,
                'isFirstFree' => true,
                'stepCostType' => CostType::DIAMOND,
                'stepCostId' => null,
                'stepCostNum' => 1500,
                'expectError' => false,
            ],
            '初回無料_2周目は有料' => [
                'costType' => CostType::DIAMOND,
                'costId' => null,
                'costNum' => 1500,
                'loopCount' => 1,
                'isFirstFree' => true,
                'stepCostType' => CostType::DIAMOND,
                'stepCostId' => null,
                'stepCostNum' => 1500,
                'expectError' => false,
            ],
            '初回無料なのに有料でエラー' => [
                'costType' => CostType::DIAMOND,
                'costId' => null,
                'costNum' => 1500,
                'loopCount' => 0,
                'isFirstFree' => true,
                'stepCostType' => CostType::DIAMOND,
                'stepCostId' => null,
                'stepCostNum' => 1500,
                'expectError' => true,
            ],
            'Free設定_正常' => [
                'costType' => CostType::FREE,
                'costId' => null,
                'costNum' => 0,
                'loopCount' => 0,
                'isFirstFree' => false,
                'stepCostType' => CostType::FREE,
                'stepCostId' => null,
                'stepCostNum' => 0,
                'expectError' => false,
            ],
            'Free設定なのに有料でエラー' => [
                'costType' => CostType::DIAMOND,
                'costId' => null,
                'costNum' => 1500,
                'loopCount' => 0,
                'isFirstFree' => false,
                'stepCostType' => CostType::FREE,
                'stepCostId' => null,
                'stepCostNum' => 0,
                'expectError' => true,
            ],
            'ダイヤ_正常' => [
                'costType' => CostType::DIAMOND,
                'costId' => null,
                'costNum' => 1500,
                'loopCount' => 0,
                'isFirstFree' => false,
                'stepCostType' => CostType::DIAMOND,
                'stepCostId' => null,
                'stepCostNum' => 1500,
                'expectError' => false,
            ],
            'ダイヤ_コスト数不一致でエラー' => [
                'costType' => CostType::DIAMOND,
                'costId' => null,
                'costNum' => 1000,
                'loopCount' => 0,
                'isFirstFree' => false,
                'stepCostType' => CostType::DIAMOND,
                'stepCostId' => null,
                'stepCostNum' => 1500,
                'expectError' => true,
            ],
            'アイテム_正常' => [
                'costType' => CostType::ITEM,
                'costId' => 'item_001',
                'costNum' => 10,
                'loopCount' => 0,
                'isFirstFree' => false,
                'stepCostType' => CostType::ITEM,
                'stepCostId' => 'item_001',
                'stepCostNum' => 10,
                'expectError' => false,
            ],
            'アイテム_コストID不一致でエラー' => [
                'costType' => CostType::ITEM,
                'costId' => 'item_002',
                'costNum' => 10,
                'loopCount' => 0,
                'isFirstFree' => false,
                'stepCostType' => CostType::ITEM,
                'stepCostId' => 'item_001',
                'stepCostNum' => 10,
                'expectError' => true,
            ],
        ];
    }

    public function testGetLotteryBox_通常枠と確定枠の取得()
    {
        // Setup
        $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';
        $prizeGroupId = 'prize_group';
        $fixedPrizeGroupId = 'fixed_prize_group';

        // ユニットマスタ作成
        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit_regular', 'rarity' => RarityType::R->value],
            ['id' => 'unit_fixed', 'rarity' => RarityType::SR->value],
        ]);

        // OprGacha作成(prize_group_idを明示的に指定)
        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_group_id' => $fixedPrizeGroupId,
        ]);

        // 通常枠景品
        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_regular',
            'weight' => 100,
        ]);

        // 確定枠景品
        OprGachaPrize::factory()->create([
            'group_id' => $fixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_fixed',
            'weight' => 100,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        $oprStepUpGachaStep = OprStepUpGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_count' => 1,
            'fixed_prize_group_id' => $fixedPrizeGroupId,
        ])->toEntity();

        // Exercise
        $lotteryBox = $this->service->getLotteryBox($oprGacha, $oprStepUpGachaStep);

        // Verify
        $regularBox = $lotteryBox->getRegularLotteryBox();
        $fixedBox = $lotteryBox->getFixedLotteryBox();

        $this->assertNotNull($regularBox);
        $this->assertNotNull($fixedBox);
        $this->assertCount(1, $regularBox);
        $this->assertCount(1, $fixedBox);

        $this->assertEquals('unit_regular', $regularBox->first()->getResourceId());
        $this->assertEquals('unit_fixed', $fixedBox->first()->getResourceId());
    }
}
