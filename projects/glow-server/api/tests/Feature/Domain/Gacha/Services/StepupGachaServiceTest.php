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
use App\Domain\Resource\Mst\Models\OprStepupGachaStepReward;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Resource\Entities\Rewards\StepupGachaStepReward;
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

    public function testGetLotteryBox_確定枠なしの場合はfixedBoxがnull()
    {
        // Setup
        $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';
        $prizeGroupId = 'prize_group';

        MstUnit::factory()->create([
            'id' => 'unit_regular',
            'rarity' => RarityType::R->value,
        ]);

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
        ]);

        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_regular',
            'weight' => 100,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_count' => 0, // 確定枠なし
            'fixed_prize_group_id' => null,
        ])->toEntity();

        // Exercise
        $lotteryBox = $this->service->getLotteryBox($oprGacha, $oprStepupGachaStep);

        // Verify - 確定枠がないのでfixedBoxはnull
        $this->assertNotNull($lotteryBox->getRegularLotteryBox());
        $this->assertNull($lotteryBox->getFixedLotteryBox());
    }

    public function testGetLotteryBox_確定枠付きで全景品が取得される()
    {
        // Setup
        $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';
        $prizeGroupId = 'prize_group';
        $fixedPrizeGroupId = 'fixed_prize_group';

        MstUnit::factory()->createMany([
            ['id' => 'unit_r', 'rarity' => RarityType::R->value],
            ['id' => 'unit_sr', 'rarity' => RarityType::SR->value],
            ['id' => 'unit_ssr', 'rarity' => RarityType::SSR->value],
        ]);

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
            'resource_id' => 'unit_r',
            'weight' => 100,
        ]);

        // 確定枠景品（R, SR, SSR全て含む）
        foreach (['unit_r', 'unit_sr', 'unit_ssr'] as $unitId) {
            OprGachaPrize::factory()->create([
                'group_id' => $fixedPrizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $unitId,
                'weight' => 100,
            ]);
        }

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

        // Verify - 全景品がフィルタなしで取得される
        $fixedBox = $lotteryBox->getFixedLotteryBox();
        $this->assertNotNull($fixedBox);
        $this->assertCount(3, $fixedBox); // R, SR, SSRの3件全て
        $resourceIds = $fixedBox->map(fn($p) => $p->getResourceId())->toArray();
        $this->assertContains('unit_r', $resourceIds);
        $this->assertContains('unit_sr', $resourceIds);
        $this->assertContains('unit_ssr', $resourceIds);
    }

    public function testGetLotteryBox_ステップのprizeGroupIdがnullの場合は親のガシャ設定を参照()
    {
        // Setup
        $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';
        $parentPrizeGroupId = 'parent_prize_group';
        $parentFixedPrizeGroupId = 'parent_fixed_prize_group';

        MstUnit::factory()->createMany([
            ['id' => 'unit_regular', 'rarity' => RarityType::R->value],
            ['id' => 'unit_fixed', 'rarity' => RarityType::SR->value],
        ]);

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $parentPrizeGroupId,
            'fixed_prize_group_id' => $parentFixedPrizeGroupId,
        ]);

        // 親のガシャ設定に景品を紐付け
        OprGachaPrize::factory()->create([
            'group_id' => $parentPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_regular',
            'weight' => 100,
        ]);

        OprGachaPrize::factory()->create([
            'group_id' => $parentFixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_fixed',
            'weight' => 100,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        // ステップ設定でprize_group_id/fixed_prize_group_idをnullに
        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'prize_group_id' => null, // null → 親のopr_gachas.prize_group_idを参照
            'fixed_prize_count' => 1,
            'fixed_prize_group_id' => null, // null → 親のopr_gachas.fixed_prize_group_idを参照
        ])->toEntity();

        // Exercise
        $lotteryBox = $this->service->getLotteryBox($oprGacha, $oprStepupGachaStep);

        // Verify - 親のグループIDの景品が取得される
        $regularBox = $lotteryBox->getRegularLotteryBox();
        $fixedBox = $lotteryBox->getFixedLotteryBox();

        $this->assertNotNull($regularBox);
        $this->assertNotNull($fixedBox);
        $this->assertCount(1, $regularBox);
        $this->assertCount(1, $fixedBox);
        $this->assertEquals('unit_regular', $regularBox->first()->getResourceId());
        $this->assertEquals('unit_fixed', $fixedBox->first()->getResourceId());
    }

    public function testProgressStep_maxLoopCountがnullの場合は無限周回可能()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';

        $stepupGacha = OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => null, // 無限周回
        ])->toEntity();

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 5, // 最終ステップ
            'loop_count' => 100, // 大量に周回していても
        ]);

        // Exercise - maxLoopCount=nullなのでエラーにならない
        $this->service->progressStep($usrGacha, $stepupGacha);

        // Verify
        $this->assertEquals(1, $usrGacha->getCurrentStepNumber()); // ステップ1に戻る
        $this->assertEquals(101, $usrGacha->getLoopCount()); // 周回数が増える
    }

    public function testInitializeAndValidate_maxLoopCountがnullの場合は周回制限なし()
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
            'max_loop_count' => null, // 無限周回
        ]);

        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUser->id,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => 1,
            'loop_count' => 100, // 大量に周回していてもエラーにならない
        ]);

        // Exercise - エラーにならない
        $state = $this->service->initializeAndValidate($usrGacha, $oprGachaId);

        // Verify
        $this->assertEquals(1, $state->getCurrentStepNumber());
        $this->assertEquals(100, $state->getLoopCount());
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

    public function testValidateCost_初回無料ステップで2周目にFREEを送信するとエラー()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $drawCount = 10;

        $oprGacha = OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ])->toEntity();

        // isFirstFree=true のステップ定義
        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'is_first_free' => true,
            'cost_type' => CostType::DIAMOND->value,
            'cost_id' => null,
            'cost_num' => 1500,
            'draw_count' => $drawCount,
        ])->toEntity();

        // Exercise & Verify
        // loopCount=1（2周目）では isFirstFree の条件を満たさないため、
        // FREEコストはステップ定義(DIAMOND)とも不一致、フォールバックにもないのでエラー
        $this->expectException(GameException::class);

        $this->service->validateCost(
            $oprGacha,
            $oprStepupGachaStep,
            CostType::FREE,   // FREEで送信
            null,
            0,
            1,   // loopCount=1（2周目）
        );
    }

    public function testGetLotteryBox_確定枠ありだがfixedPrizeGroupIdがnullでエラー()
    {
        // Setup
        $this->createUsrUser();
        $oprGachaId = 'stepup_gacha_001';
        $prizeGroupId = 'prize_group';

        MstUnit::factory()->create([
            'id' => 'unit_regular',
            'rarity' => RarityType::R->value,
        ]);

        // opr_gacha: fixed_prize_group_idもnull
        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_group_id' => null, // 親もnull
        ]);

        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_regular',
            'weight' => 100,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        // fixed_prize_count > 0 だが fixed_prize_group_id = null でステップ側も親側もnull
        $oprStepupGachaStep = OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_count' => 3,              // 確定枠あり
            'fixed_prize_group_id' => null,          // ステップ側null
        ])->toEntity();

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_BOX_IS_EMPTY);

        $this->service->getLotteryBox($oprGacha, $oprStepupGachaStep);
    }

    public function testAddStepRewards_おまけ報酬がRewardDelegatorに追加される()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $stepNumber = 2;
        $loopCount = 0;

        // おまけ報酬データ作成
        OprStepupGachaStepReward::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => $stepNumber,
            'loop_count_target' => null,  // 全周回対象
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 1000,
        ]);

        OprStepupGachaStepReward::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => $stepNumber,
            'loop_count_target' => null,
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 50,
        ]);

        // Exercise
        $this->service->addStepRewards($oprGachaId, $stepNumber, $loopCount);

        // Verify - RewardManagerにStepupGachaStepRewardが追加されていることを確認
        $rewardManager = app(\App\Domain\Reward\Managers\RewardManager::class);
        $addedRewards = $rewardManager->getNeedToSendRewards();
        $stepRewards = $addedRewards->filter(fn($r) => $r instanceof StepupGachaStepReward);

        $this->assertCount(2, $stepRewards);
    }

    public function testAddStepRewards_おまけ報酬がないステップでは何も追加されない()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $stepNumber = 3;
        $loopCount = 0;

        // おまけ報酬データは作成しない（他のステップ用のものだけ）
        OprStepupGachaStepReward::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,   // ステップ1用のおまけ
            'loop_count_target' => null,
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 500,
        ]);

        // Exercise
        $this->service->addStepRewards($oprGachaId, $stepNumber, $loopCount);

        // Verify - StepupGachaStepRewardは追加されていない
        $rewardManager = app(\App\Domain\Reward\Managers\RewardManager::class);
        $addedRewards = $rewardManager->getNeedToSendRewards();
        $stepRewards = $addedRewards->filter(fn($r) => $r instanceof StepupGachaStepReward);

        $this->assertCount(0, $stepRewards);
    }

    public function testAddStepRewards_loopCountTargetで周回数フィルタされる()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_001';
        $stepNumber = 1;

        // 1周目のみのおまけ
        OprStepupGachaStepReward::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => $stepNumber,
            'loop_count_target' => 0,   // 1周目（loopCount=0）のみ
            'resource_type' => RewardType::COIN->value,
            'resource_id' => null,
            'resource_amount' => 1000,
        ]);

        // 全周回のおまけ
        OprStepupGachaStepReward::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => $stepNumber,
            'loop_count_target' => null,   // 全周回
            'resource_type' => RewardType::FREE_DIAMOND->value,
            'resource_id' => null,
            'resource_amount' => 50,
        ]);

        // Exercise - 2周目（loopCount=1）で実行
        $this->service->addStepRewards($oprGachaId, $stepNumber, 1);

        // Verify - loopCountTarget=0（1周目のみ）は含まれず、NULLのものだけ追加される
        $rewardManager = app(\App\Domain\Reward\Managers\RewardManager::class);
        $addedRewards = $rewardManager->getNeedToSendRewards();
        $stepRewards = $addedRewards->filter(fn($r) => $r instanceof StepupGachaStepReward);

        $this->assertCount(1, $stepRewards);
    }

    /**
     * 全ての報酬タイプでおまけ報酬が獲得できることを検証するDataProvider
     */
    public static function allRewardTypesProvider(): array
    {
        return [
            'Coin（コイン）' => [
                'resourceType' => RewardType::COIN->value,
                'resourceId' => null,
                'resourceAmount' => 1000,
            ],
            'FreeDiamond（無償プリズム）' => [
                'resourceType' => RewardType::FREE_DIAMOND->value,
                'resourceId' => null,
                'resourceAmount' => 50,
            ],
            'Stamina（スタミナ回復）' => [
                'resourceType' => RewardType::STAMINA->value,
                'resourceId' => null,
                'resourceAmount' => 5,
            ],
            'Exp（経験値）' => [
                'resourceType' => RewardType::EXP->value,
                'resourceId' => null,
                'resourceAmount' => 300,
            ],
            'Item（アイテム）' => [
                'resourceType' => RewardType::ITEM->value,
                'resourceId' => 'item_reward_001',
                'resourceAmount' => 3,
            ],
            'Emblem（エンブレム）' => [
                'resourceType' => RewardType::EMBLEM->value,
                'resourceId' => 'emblem_reward_001',
                'resourceAmount' => 1,
            ],
            'Unit（キャラ）' => [
                'resourceType' => RewardType::UNIT->value,
                'resourceId' => 'unit_reward_001',
                'resourceAmount' => 1,
            ],
            'Artwork（原画）' => [
                'resourceType' => RewardType::ARTWORK->value,
                'resourceId' => 'artwork_reward_001',
                'resourceAmount' => 1,
            ],
        ];
    }

    #[DataProvider('allRewardTypesProvider')]
    public function testAddStepRewards_全報酬タイプが獲得できる(
        string $resourceType,
        ?string $resourceId,
        int $resourceAmount,
    ): void {
        // Setup
        $oprGachaId = 'stepup_gacha_reward_type_test';
        $stepNumber = 1;
        $loopCount = 0;

        OprStepupGachaStepReward::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => $stepNumber,
            'loop_count_target' => null,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'resource_amount' => $resourceAmount,
        ]);

        // Exercise
        $this->service->addStepRewards($oprGachaId, $stepNumber, $loopCount);

        // Verify
        $rewardManager = app(\App\Domain\Reward\Managers\RewardManager::class);
        $addedRewards = $rewardManager->getNeedToSendRewards();
        $stepRewards = $addedRewards->filter(fn($r) => $r instanceof StepupGachaStepReward);

        // $resourceTypeタイプの報酬が追加されていること
        $this->assertCount(1, $stepRewards);

        /** @var StepupGachaStepReward $reward */
        $reward = $stepRewards->first();
        $this->assertEquals($resourceType, $reward->getType());
        $this->assertEquals($resourceId, $reward->getResourceId());
        $this->assertEquals($resourceAmount, $reward->getAmount());
        $this->assertEquals($oprGachaId, $reward->getOprGachaId());
        $this->assertEquals($stepNumber, $reward->getStepNumber());
        $this->assertEquals($loopCount, $reward->getLoopCount());
    }

    public function test_get_prizes_全ステップの通常枠と確定枠の提供割合が返される()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_prizes_001';
        $regularPrizeGroupId = 'regular_prize_group';
        $fixedPrizeGroupId = 'fixed_prize_group';

        MstUnit::factory()->createMany([
            ['id' => 'unit_r', 'rarity' => RarityType::R->value],
            ['id' => 'unit_sr', 'rarity' => RarityType::SR->value],
            ['id' => 'unit_ssr', 'rarity' => RarityType::SSR->value],
        ]);

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $regularPrizeGroupId,
            'fixed_prize_group_id' => $fixedPrizeGroupId,
        ]);

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 3,
            'max_loop_count' => null,
        ]);

        // 通常枠景品（R, SR, SSR）
        OprGachaPrize::factory()->create([
            'group_id' => $regularPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_r',
            'weight' => 700,
        ]);
        OprGachaPrize::factory()->create([
            'group_id' => $regularPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_sr',
            'weight' => 250,
        ]);
        OprGachaPrize::factory()->create([
            'group_id' => $regularPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_ssr',
            'weight' => 50,
        ]);

        // 確定枠景品（SR, SSR）
        OprGachaPrize::factory()->create([
            'group_id' => $fixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_sr',
            'weight' => 800,
        ]);
        OprGachaPrize::factory()->create([
            'group_id' => $fixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_ssr',
            'weight' => 200,
        ]);

        // ステップ1: 確定枠なし
        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'fixed_prize_count' => 0,
            'prize_group_id' => null,
            'fixed_prize_group_id' => null,
        ]);

        // ステップ2: 確定枠1
        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 2,
            'draw_count' => 10,
            'fixed_prize_count' => 1,
            'prize_group_id' => null,
            'fixed_prize_group_id' => null,
        ]);

        // ステップ3: 確定枠2
        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 3,
            'draw_count' => 10,
            'fixed_prize_count' => 2,
            'prize_group_id' => null,
            'fixed_prize_group_id' => null,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        // Exercise
        $result = $this->service->getPrizes($oprGacha);

        // Verify
        $this->assertCount(3, $result);

        // ステップ1: 通常枠の提供割合あり、確定枠は空
        $step1 = $result->first(fn($s) => $s->getStepNumber() === 1);
        $step1Response = $step1->formatToResponse();
        $this->assertEquals(10, $step1->getDrawCount());
        $this->assertEquals(0, $step1->getFixedPrizeCount());
        $this->assertCount(3, $step1->getRarityProbabilities());
        $this->assertCount(3, $step1->getProbabilityGroups());
        $this->assertEquals(0, $step1Response['fixedProbabilities']['fixedCount']);
        $this->assertCount(0, $step1Response['fixedProbabilities']['rarityProbabilities']);
        $this->assertCount(0, $step1Response['fixedProbabilities']['probabilityGroups']);

        // 通常枠の確率: R=700/1000=70.0%, SR=250/1000=25.0%, SSR=50/1000=5.0%
        $step1Rarities = collect($step1Response['rarityProbabilities']);
        $this->assertEquals(70.0, $step1Rarities->firstWhere('rarity', RarityType::R->value)['probability']);
        $this->assertEquals(25.0, $step1Rarities->firstWhere('rarity', RarityType::SR->value)['probability']);
        $this->assertEquals(5.0, $step1Rarities->firstWhere('rarity', RarityType::SSR->value)['probability']);

        // ステップ2: 通常枠と確定枠の両方の提供割合あり
        $step2 = $result->first(fn($s) => $s->getStepNumber() === 2);
        $step2Response = $step2->formatToResponse();
        $this->assertEquals(10, $step2->getDrawCount());
        $this->assertEquals(1, $step2->getFixedPrizeCount());
        $this->assertCount(3, $step2->getRarityProbabilities());
        $this->assertCount(3, $step2->getProbabilityGroups());
        $this->assertEquals(1, $step2Response['fixedProbabilities']['fixedCount']);
        $this->assertCount(2, $step2Response['fixedProbabilities']['rarityProbabilities']);
        $this->assertCount(2, $step2Response['fixedProbabilities']['probabilityGroups']);

        // 確定枠の確率: SR=800/1000=80.0%, SSR=200/1000=20.0%
        $step2FixedRarities = collect($step2Response['fixedProbabilities']['rarityProbabilities']);
        $this->assertEquals(80.0, $step2FixedRarities->firstWhere('rarity', RarityType::SR->value)['probability']);
        $this->assertEquals(20.0, $step2FixedRarities->firstWhere('rarity', RarityType::SSR->value)['probability']);

        // ステップ3: 通常枠と確定枠の両方の提供割合あり
        $step3 = $result->first(fn($s) => $s->getStepNumber() === 3);
        $step3Response = $step3->formatToResponse();
        $this->assertEquals(10, $step3->getDrawCount());
        $this->assertEquals(2, $step3->getFixedPrizeCount());
        $this->assertCount(3, $step3->getRarityProbabilities());
        $this->assertCount(3, $step3->getProbabilityGroups());
        $this->assertEquals(2, $step3Response['fixedProbabilities']['fixedCount']);
        $this->assertCount(2, $step3Response['fixedProbabilities']['rarityProbabilities']);
        $this->assertCount(2, $step3Response['fixedProbabilities']['probabilityGroups']);

        // ステップ3の確定枠もステップ2と同じ（親のfixedPrizeGroupIdにフォールバック）
        $step3FixedRarities = collect($step3Response['fixedProbabilities']['rarityProbabilities']);
        $this->assertEquals(80.0, $step3FixedRarities->firstWhere('rarity', RarityType::SR->value)['probability']);
        $this->assertEquals(20.0, $step3FixedRarities->firstWhere('rarity', RarityType::SSR->value)['probability']);
    }

    public function test_get_prizes_ステップごとに異なるprizeGroupIdで通常枠が異なる()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_prizes_002';
        $parentPrizeGroupId = 'parent_prize_group';
        $step2PrizeGroupId = 'step2_prize_group';
        $fixedPrizeGroupId = 'fixed_prize_group';
        $step2FixedPrizeGroupId = 'step2_fixed_prize_group';

        MstUnit::factory()->createMany([
            ['id' => 'unit_r_parent', 'rarity' => RarityType::R->value],
            ['id' => 'unit_sr_parent', 'rarity' => RarityType::SR->value],
            ['id' => 'unit_ssr_step2', 'rarity' => RarityType::SSR->value],
            ['id' => 'unit_sr_fixed', 'rarity' => RarityType::SR->value],
        ]);

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $parentPrizeGroupId,
            'fixed_prize_group_id' => $fixedPrizeGroupId,
        ]);

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 2,
            'max_loop_count' => null,
        ]);

        // 親の通常枠景品（R, SR）
        OprGachaPrize::factory()->createMany([
            ['group_id' => $parentPrizeGroupId, 'resource_type' => RewardType::UNIT, 'resource_id' => 'unit_r_parent', 'weight' => 800],
            ['group_id' => $parentPrizeGroupId, 'resource_type' => RewardType::UNIT, 'resource_id' => 'unit_sr_parent', 'weight' => 200],
        ]);

        // ステップ2固有の通常枠景品（SSRのみ）
        OprGachaPrize::factory()->create([
            'group_id' => $step2PrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_ssr_step2',
            'weight' => 1000,
        ]);

        // 親の確定枠景品（SR）
        OprGachaPrize::factory()->create([
            'group_id' => $fixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_sr_fixed',
            'weight' => 1000,
        ]);

        // ステップ2固有の確定枠景品（SSRのみ）
        OprGachaPrize::factory()->create([
            'group_id' => $step2FixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_ssr_step2',
            'weight' => 1000,
        ]);

        // ステップ1: 親のprizeGroupIdを使用
        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'fixed_prize_count' => 0,
            'prize_group_id' => null,
            'fixed_prize_group_id' => null,
        ]);

        // ステップ2: 独自のprizeGroupIdとfixedPrizeGroupIdを使用
        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 2,
            'draw_count' => 10,
            'fixed_prize_count' => 1,
            'prize_group_id' => $step2PrizeGroupId,
            'fixed_prize_group_id' => $step2FixedPrizeGroupId,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        // Exercise
        $result = $this->service->getPrizes($oprGacha);

        // Verify
        $this->assertCount(2, $result);

        // ステップ1: 親の通常枠（R=800/1000=80.0%, SR=200/1000=20.0%）
        $step1 = $result->first(fn($s) => $s->getStepNumber() === 1);
        $step1Response = $step1->formatToResponse();
        $this->assertCount(2, $step1->getRarityProbabilities());
        $step1Rarities = collect($step1Response['rarityProbabilities']);
        $this->assertEquals(80.0, $step1Rarities->firstWhere('rarity', RarityType::R->value)['probability']);
        $this->assertEquals(20.0, $step1Rarities->firstWhere('rarity', RarityType::SR->value)['probability']);
        $this->assertCount(0, $step1Response['fixedProbabilities']['rarityProbabilities']);

        // ステップ2: ステップ固有の通常枠（SSR=1000/1000=100.0%）
        $step2 = $result->first(fn($s) => $s->getStepNumber() === 2);
        $step2Response = $step2->formatToResponse();
        $this->assertCount(1, $step2->getRarityProbabilities());
        $step2Rarities = collect($step2Response['rarityProbabilities']);
        $this->assertEquals(100.0, $step2Rarities->firstWhere('rarity', RarityType::SSR->value)['probability']);

        // ステップ2確定枠: ステップ固有（SSR=1000/1000=100.0%）
        $this->assertEquals(1, $step2Response['fixedProbabilities']['fixedCount']);
        $step2FixedRarities = collect($step2Response['fixedProbabilities']['rarityProbabilities']);
        $this->assertEquals(RarityType::SSR->value, $step2FixedRarities[0]['rarity']);
        $this->assertEquals(100.0, $step2FixedRarities[0]['probability']);

        $step2FixedGroups = collect($step2Response['fixedProbabilities']['probabilityGroups']);
        $this->assertCount(1, $step2FixedGroups);
        $this->assertEquals('unit_ssr_step2', $step2FixedGroups[0]['prizes'][0]['resourceId']);
        $this->assertEquals(100.0, $step2FixedGroups[0]['prizes'][0]['probability']);
    }

    public function test_get_prizes_ステップアップ以外のガシャは空コレクションを返す()
    {
        // Setup
        $oprGachaId = 'normal_gacha_001';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::NORMAL->value,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        // Exercise
        $result = $this->service->getPrizes($oprGacha);

        // Verify
        $this->assertTrue($result->isEmpty());
    }

    public function test_get_prizes_formatToResponseで通常枠と確定枠が正しい構造で返される()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_prizes_fmt';
        $regularPrizeGroupId = 'regular_prize_group_fmt';
        $fixedPrizeGroupId = 'fixed_prize_group_fmt';

        MstUnit::factory()->createMany([
            ['id' => 'unit_r_fmt', 'rarity' => RarityType::R->value],
            ['id' => 'unit_ssr_fmt', 'rarity' => RarityType::SSR->value],
        ]);

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $regularPrizeGroupId,
            'fixed_prize_group_id' => $fixedPrizeGroupId,
        ]);

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 1,
            'max_loop_count' => null,
        ]);

        // 通常枠景品
        OprGachaPrize::factory()->create([
            'group_id' => $regularPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_r_fmt',
            'weight' => 1000,
        ]);

        // 確定枠景品
        OprGachaPrize::factory()->create([
            'group_id' => $fixedPrizeGroupId,
            'resource_type' => RewardType::UNIT,
            'resource_id' => 'unit_ssr_fmt',
            'weight' => 1000,
        ]);

        // ステップ1: 確定枠1
        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'fixed_prize_count' => 1,
            'prize_group_id' => null,
            'fixed_prize_group_id' => null,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        // Exercise
        $result = $this->service->getPrizes($oprGacha);
        $responseData = $result->first()->formatToResponse();

        // Verify - レスポンス構造に通常枠と確定枠が含まれる
        $this->assertArrayHasKey('stepNumber', $responseData);
        $this->assertArrayHasKey('drawCount', $responseData);
        $this->assertArrayHasKey('rarityProbabilities', $responseData);
        $this->assertArrayHasKey('probabilityGroups', $responseData);
        $this->assertArrayHasKey('fixedProbabilities', $responseData);
        $this->assertArrayHasKey('fixedCount', $responseData['fixedProbabilities']);
        $this->assertArrayHasKey('rarityProbabilities', $responseData['fixedProbabilities']);
        $this->assertArrayHasKey('probabilityGroups', $responseData['fixedProbabilities']);

        // 通常枠: R=1000/1000=100.0%
        $this->assertCount(1, $responseData['rarityProbabilities']);
        $this->assertEquals(RarityType::R->value, $responseData['rarityProbabilities'][0]['rarity']);
        $this->assertEquals(100.0, $responseData['rarityProbabilities'][0]['probability']);
        $this->assertCount(1, $responseData['probabilityGroups']);
        $this->assertEquals(RarityType::R->value, $responseData['probabilityGroups'][0]['rarity']);
        $this->assertEquals('unit_r_fmt', $responseData['probabilityGroups'][0]['prizes'][0]['resourceId']);
        $this->assertEquals(100.0, $responseData['probabilityGroups'][0]['prizes'][0]['probability']);

        // 確定枠: SSR=1000/1000=100.0%
        $this->assertEquals(1, $responseData['fixedProbabilities']['fixedCount']);
        $this->assertCount(1, $responseData['fixedProbabilities']['rarityProbabilities']);
        $this->assertEquals(RarityType::SSR->value, $responseData['fixedProbabilities']['rarityProbabilities'][0]['rarity']);
        $this->assertEquals(100.0, $responseData['fixedProbabilities']['rarityProbabilities'][0]['probability']);
        $this->assertCount(1, $responseData['fixedProbabilities']['probabilityGroups']);
        $this->assertEquals(RarityType::SSR->value, $responseData['fixedProbabilities']['probabilityGroups'][0]['rarity']);
        $this->assertEquals('unit_ssr_fmt', $responseData['fixedProbabilities']['probabilityGroups'][0]['prizes'][0]['resourceId']);
        $this->assertEquals(100.0, $responseData['fixedProbabilities']['probabilityGroups'][0]['prizes'][0]['probability']);
    }

    public function test_get_prizes_存在しないグループIDでエラーが発生する()
    {
        // Setup
        $oprGachaId = 'stepup_gacha_prizes_invalid';
        $nonExistentPrizeGroupId = 'non_existent_group';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $nonExistentPrizeGroupId,
            'fixed_prize_group_id' => null,
        ]);

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 1,
            'max_loop_count' => null,
        ]);

        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => 1,
            'draw_count' => 10,
            'fixed_prize_count' => 0,
            'prize_group_id' => null,
            'fixed_prize_group_id' => null,
        ]);

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_BOX_IS_EMPTY);

        $this->service->getPrizes($oprGacha);
    }
}
