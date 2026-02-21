<?php

namespace Feature\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Services\StepupGachaService;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Domain\Resource\Mst\Models\OprStepupGacha;
use App\Domain\Resource\Mst\Models\OprStepupGachaStep;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StepupGachaServiceTest extends TestCase
{
    private StepupGachaService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(StepupGachaService::class);
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
        $reflection = new \ReflectionClass(StepupGachaService::class);
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

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        OprStepupGachaStep::factory()->create([
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
        $state = $this->service->initializeAndValidate($usrGacha, $oprGachaId);

        // Verify
        $this->assertEquals(1, $state->getCurrentStepNumber());
        $this->assertEquals(0, $state->getLoopCount());
        $this->assertEquals(1, $state->getStepupGachaStep()->getStepNumber());
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

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
        ]);

        OprStepupGachaStep::factory()->create([
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

        $this->service->initializeAndValidate($usrGacha, $oprGachaId, 1); // クライアント側は1だと主張
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

        OprStepupGacha::factory()->create([
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

        $this->service->initializeAndValidate($usrGacha, $oprGachaId);
    }

    public function testValidateAndResolvePlayNum_FREE時はdrawCountを返す()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $drawCount = 10;

        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => $drawCount,
            'cost_type' => CostType::DIAMOND->value,
            'is_first_free' => false,
        ])->toEntity();

        // Exercise - costTypeがFREEの場合、playNum=1でもdrawCountが返る
        $result = $this->service->validateAndResolvePlayNum($oprStepupGachaStep, 1, CostType::FREE);

        // Verify
        $this->assertEquals($drawCount, $result);
    }

    public function testValidateAndResolvePlayNum_通常コストでplayNum一致()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $drawCount = 10;

        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => $drawCount,
            'cost_type' => CostType::DIAMOND->value,
            'is_first_free' => false,
        ])->toEntity();

        // Exercise
        $result = $this->service->validateAndResolvePlayNum($oprStepupGachaStep, $drawCount, CostType::DIAMOND);

        // Verify
        $this->assertEquals($drawCount, $result);
    }

    public function testValidateAndResolvePlayNum_通常コストでplayNum不一致エラー()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $drawCount = 10;

        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => $drawCount,
            'cost_type' => CostType::DIAMOND->value,
            'is_first_free' => false,
        ])->toEntity();

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_NOT_EXPECTED_PLAY_NUM);

        $this->service->validateAndResolvePlayNum($oprStepupGachaStep, 5, CostType::DIAMOND);
    }

    public function testProgressStep_次のステップへ進行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';

        $stepupGacha = OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ])->toEntity();

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 2,
            'loop_count' => 0,
        ]);

        // Exercise
        $this->service->progressStep($usrGacha, $stepupGacha);

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

        $stepupGacha = OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => 3,
        ])->toEntity();

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => $maxStepNumber,
            'loop_count' => 1,
        ]);

        // Exercise
        $this->service->progressStep($usrGacha, $stepupGacha);

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

        $stepupGacha = OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => $maxLoopCount,
        ])->toEntity();

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => $maxStepNumber,
            'loop_count' => $maxLoopCount,
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_STEPUP_MAX_LOOP_COUNT_EXCEEDED);

        $this->service->progressStep($usrGacha, $stepupGacha);
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
        $drawCount = 10;

        $oprGacha = OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ])->toEntity();

        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'is_first_free' => $isFirstFree,
            'cost_type' => $stepCostType->value,
            'cost_id' => $stepCostId,
            'cost_num' => $stepCostNum,
            'draw_count' => $drawCount,
        ])->toEntity();

        // Exercise & Verify
        if ($expectError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::GACHA_UNJUST_COSTS);
        }

        $this->service->validateCost($oprGacha, $oprStepupGachaStep, $costType, $costId, $costNum, $loopCount);

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

    public function test_validate_cost_補填チケットでopr_gacha_use_resourcesからフォールバック成功()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $drawCount = 10;
        $ticketId = 'compensation_ticket_001';
        $ticketCostNum = 1;

        $oprGacha = OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ])->toEntity();

        // ステップ定義はダイヤモンドコスト
        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'is_first_free' => false,
            'cost_type' => CostType::DIAMOND->value,
            'cost_id' => null,
            'cost_num' => 1500,
            'draw_count' => $drawCount,
        ])->toEntity();

        // opr_gacha_use_resourcesに補填チケットのレコードを追加
        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'cost_type' => CostType::ITEM->value,
            'cost_id' => $ticketId,
            'cost_num' => $ticketCostNum,
            'draw_count' => $drawCount,
            'cost_priority' => 1,
        ]);

        // Exercise & Verify - ステップ定義はDiamondだがItemチケットで通る
        $this->service->validateCost(
            $oprGacha,
            $oprStepupGachaStep,
            CostType::ITEM,
            $ticketId,
            $ticketCostNum,
            0,
        );
        $this->assertTrue(true);
    }

    public function testValidateCost_opr_gacha_use_resourcesにもレコードなしでエラー()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $drawCount = 10;

        $oprGacha = OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ])->toEntity();

        // ステップ定義はダイヤモンドコスト
        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'is_first_free' => false,
            'cost_type' => CostType::DIAMOND->value,
            'cost_id' => null,
            'cost_num' => 1500,
            'draw_count' => $drawCount,
        ])->toEntity();

        // opr_gacha_use_resourcesにはITEMのレコードを登録しない

        // Exercise & Verify - ステップ定義にもuse_resourcesにもマッチしないのでエラー
        // GachaService::validateCostのITEMパスでdraw_count=1のフォールバック検索時に
        // リポジトリがMST_NOT_FOUNDをスローする
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        $this->service->validateCost(
            $oprGacha,
            $oprStepupGachaStep,
            CostType::ITEM,
            'nonexistent_ticket',
            1,
            0,
        );
    }

    public function test_validate_cost_補填チケット1回分マスタで回数分乗算()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $drawCount = 10;
        $ticketId = 'compensation_ticket_001';
        $ticketCostPerDraw = 1;

        $oprGacha = OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ])->toEntity();

        // ステップ定義はダイヤモンドコスト
        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'is_first_free' => false,
            'cost_type' => CostType::DIAMOND->value,
            'cost_id' => null,
            'cost_num' => 1500,
            'draw_count' => $drawCount,
        ])->toEntity();

        // opr_gacha_use_resourcesにdraw_count=1のレコードのみ登録
        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'cost_type' => CostType::ITEM->value,
            'cost_id' => $ticketId,
            'cost_num' => $ticketCostPerDraw,
            'draw_count' => 1,
            'cost_priority' => 1,
        ]);

        // Exercise & Verify - 1回分 × 10回 = 10が期待コスト
        $this->service->validateCost(
            $oprGacha,
            $oprStepupGachaStep,
            CostType::ITEM,
            $ticketId,
            $ticketCostPerDraw * $drawCount, // 10
            0,
        );
        $this->assertTrue(true);
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

        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_count' => 1,
            'fixed_prize_group_id' => $fixedPrizeGroupId,
        ])->toEntity();

        // Exercise
        $lotteryBox = $this->service->getLotteryBox($oprGacha, $oprStepupGachaStep);

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
