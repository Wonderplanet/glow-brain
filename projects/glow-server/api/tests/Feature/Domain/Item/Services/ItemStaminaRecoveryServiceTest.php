<?php

namespace Tests\Feature\Domain\Item\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Item\Models\LogItem;
use App\Domain\Item\Services\ItemStaminaRecoveryService;
use App\Domain\Resource\Constants\MstConfigConstant;
use App\Domain\Resource\Log\Enums\LogResourceActionType;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstShopPass;
use App\Domain\Resource\Mst\Models\MstShopPassEffect;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Shop\Enums\PassEffectType;
use App\Domain\Shop\Models\UsrShopPass;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ItemStaminaRecoveryServiceTest extends TestCase
{
    private ItemStaminaRecoveryService $itemStaminaRecoveryService;

    public function setUp(): void
    {
        parent::setUp();
        $this->itemStaminaRecoveryService = app(ItemStaminaRecoveryService::class);
    }

    /**
     * スタミナ回復アイテムのテストデータ作成
     */
    private function createStaminaRecoveryTestData(string $usrUserId, int $level, int $levelStamina): void
    {
        // レベル別スタミナ上限
        MstUserLevel::factory()->create([
            'level' => $level,
            'stamina' => $levelStamina,
            'exp' => 0,
        ]);

        // スタミナ回復時間設定
        MstConfig::factory()->create([
            'key' => MstConfigConstant::RECOVERY_STAMINA_MINUTE,
            'value' => UserConstant::RECOVERY_STAMINA_MINUTE,
        ]);

        // スタミナ最大所持数設定（システム上限）
        MstConfig::factory()->create([
            'key' => MstConfigConstant::USER_STAMINA_MAX_AMOUNT,
            'value' => UserConstant::MAX_STAMINA,
        ]);
    }

    /**
     * スタミナ回復アイテム正常系のテストデータ
     *
     * @return array<string, array{currentStamina: int, expectedStamina: int}>
     */
    public static function params_test_applyStaminaRecoveryPercent_正常に回復する(): array
    {
        return [
            '基本ケース（スタミナ50から100へ回復）' => [
                'currentStamina' => 50,
                'expectedStamina' => 100, // 50 + floor(100 * 50 / 100) = 100
            ],
            'スタミナ0から回復' => [
                'currentStamina' => 0,
                'expectedStamina' => 50, // 0 + floor(100 * 50 / 100) = 50
            ],
        ];
    }

    #[DataProvider('params_test_applyStaminaRecoveryPercent_正常に回復する')]
    public function test_applyStaminaRecoveryPercent_正常に回復する(
        int $currentStamina,
        int $expectedStamina,
    ): void {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100;
        $effectValue = 50; // 50%回復
        $initialAmount = 10;

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItemId = 'staminaRecoveryItem';
        $mstItem = MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => $effectValue,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => $initialAmount,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedStamina, $usrUserParameter->getStamina());

        // アイテムが1個消費されたことを確認
        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', $mstItemId)
            ->first();
        $this->assertEquals($initialAmount - 1, $usrItem->getAmount());

        // log_items にログが記録されることを確認
        $logItem = LogItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', $mstItemId)
            ->first();
        $this->assertNotNull($logItem, 'log_items にログが記録されていること');
        $this->assertEquals(LogResourceActionType::USE->value, $logItem->action_type);
        $this->assertEquals($initialAmount, $logItem->before_amount);
        $this->assertEquals($initialAmount - 1, $logItem->after_amount);
        $this->assertEquals(LogResourceTriggerSource::USER_STAMINA_RECOVERY_COST->value, $logItem->trigger_source);
    }

    public function test_applyStaminaRecoveryPercent_複数個同時使用で回復する()
    {
        // Setup
        // 例: 現在50、上限180、50%回復 → 1個あたり90回復
        // 2個使用 → 50 + 180 = 230 → OK（上限180を超えてもOK）
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 180;
        $currentStamina = 50;
        $effectValue = 50; // 50%回復
        $amount = 2;

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryItem',
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => $effectValue,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise - 2個同時使用
        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            $amount, // 2個
            $now,
        );
        $this->saveAll();

        // Verify
        // 1個あたり回復量 = floor(180 * 50 / 100) = 90
        // 2個使用 → 90 * 2 = 180
        // 50 + 180 = 230（上限180を超えてもOK）
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(50 + 90 + 90, $usrUserParameter->getStamina());

        // アイテムが2個消費されたことを確認
        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', 'staminaRecoveryItem')
            ->first();
        $this->assertEquals(8, $usrItem->getAmount());
    }

    public function test_applyStaminaRecoveryPercent_システム上限で満タン時はエラー()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100;
        $currentStamina = 999; // システム上限999で満タン

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryItem',
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => 50,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_STAMINA_FULL);

        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
    }

    public function test_applyStaminaRecoveryPercent_自然回復後にシステム上限で満タンになる場合はエラー()
    {
        // Setup
        // スタミナ998で、stamina_updated_atがRECOVERY_STAMINA_MINUTE分前の場合、
        // 自然回復で+1されて999（システム上限で満タン）になるパターン
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 1000; // このテストのみ特別にシステム上限999をテストするため999以上に設定
        $currentStamina = 998; // DBの値は満タンではない

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryItem',
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => 50,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            // RECOVERY_STAMINA_MINUTE分前に設定 → 自然回復で+1
            'stamina_updated_at' => $now->subMinutes(UserConstant::RECOVERY_STAMINA_MINUTE)->toDateTimeString(),
        ]);

        // Exercise & Verify
        // 自然回復後: 998 + 1 = 999（システム上限で満タン）
        // 満タン状態でアイテム使用しようとするとエラー
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_STAMINA_FULL);

        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
    }

    public function test_applyStaminaRecoveryPercent_使用可能個数を超える場合はエラー()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100;
        $currentStamina = 950;
        $effectValue = 50; // 50%回復 = floor(100 * 50 / 100) = 50回復

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryItem',
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => $effectValue,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // 基礎回復量 = floor(100 * 50 / 100) = 50
        // 使用可能個数 = max(1, ceil((999 - 950) / 50)) = max(1, 0) = 1
        // amount=2 を指定すると使用可能個数(1)を超えるためエラー
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);

        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            2, // 使用可能個数(1)を超えるためエラー
            $now,
        );
    }

    public function test_applyStaminaRecoveryPercent_システム上限到達時は部分回復()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 300; // ユーザー上限
        $currentStamina = 950; // システム上限999に近い値
        $effectValue = 100; // 100%回復（300ポイント回復 → 950+300=1250 > 999で部分回復）

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryItem',
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => $effectValue,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        // システム上限999を超える場合は部分回復されて999になる
        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
        $this->saveAll();

        // Verify
        // 950 + 300 → 999（251切り捨て、システム上限でカット）
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(999, $usrUserParameter->getStamina());
    }

    public function test_applyStaminaRecoveryPercent_部分回復時に複数個使用可能()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100; // ユーザー上限100
        $currentStamina = 800; // システム上限999に対して残り199
        $effectValue = 100; // 100%回復（100ポイント回復）

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryItem',
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => $effectValue,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        // 100% = 100ポイント回復
        // maxUsable = max(1, ceil((999-800)/100)) = max(1, ceil(1.99)) = 2
        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            2, // 2個使用
            $now,
        );
        $this->saveAll();

        // Verify
        // 800 + 100*2 → 999（1切り捨て、システム上限でカット）
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(999, $usrUserParameter->getStamina());

        // アイテムが2個消費されている
        $usrItem = UsrItem::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(8, $usrItem->getAmount());
    }

    /**
     * ショップパス効果が正しく加算されるか
     */
    public function test_applyStaminaRecoveryPercent_ショップパス効果でスタミナ上限が増加する()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100;
        $currentStamina = 50;
        $effectValue = 50; // 50%回復
        $shopPassStaminaAddLimit = 20; // ショップパスでスタミナ上限+20

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        // ショップパスマスタを作成
        $mstShopPassId = 'test_shop_pass';
        MstShopPass::factory()->create([
            'id' => $mstShopPassId,
            'opr_product_id' => 'test_product',
            'pass_duration_days' => 30,
        ]);

        // ショップパス効果を設定（スタミナ上限加算）
        MstShopPassEffect::factory()->create([
            'mst_shop_pass_id' => $mstShopPassId,
            'effect_type' => PassEffectType::STAMINA_ADD_RECOVERY_LIMIT->value,
            'effect_value' => $shopPassStaminaAddLimit,
        ]);

        // ユーザーにショップパスを付与
        UsrShopPass::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_shop_pass_id' => $mstShopPassId,
            'daily_reward_received_count' => 0,
            'daily_latest_received_at' => $now->toDateTimeString(),
            'start_at' => $now->toDateTimeString(),
            'end_at' => $now->addDays(30)->toDateTimeString(), // 30日間有効
        ]);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryItemWithPass',
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => $effectValue,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryItemWithPass',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
        $this->saveAll();

        // Verify
        // ショップパス効果後のスタミナ上限 = 100 + 20 = 120
        // 回復量 = floor(120 * 50 / 100) = 60
        // 50 + 60 = 110
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(110, $usrUserParameter->getStamina());
    }

    /**
     * ========================================
     * applyStaminaRecoveryFixed のテスト
     * ========================================
     */

    public function test_applyStaminaRecoveryFixed_正常に回復する()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100;
        $currentStamina = 50;
        $fixedAmount = 30; // 固定30回復
        $initialAmount = 10;

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItemId = 'staminaRecoveryFixedItem';
        $mstItem = MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::STAMINA_RECOVERY_FIXED->value,
            'effect_value' => $fixedAmount,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => $initialAmount,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $this->itemStaminaRecoveryService->applyStaminaRecoveryFixed(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(80, $usrUserParameter->getStamina()); // 50 + 30 = 80

        // アイテムが1個消費されたことを確認
        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', $mstItemId)
            ->first();
        $this->assertEquals($initialAmount - 1, $usrItem->getAmount());

        // log_items にログが記録されることを確認
        $logItem = LogItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', $mstItemId)
            ->first();
        $this->assertNotNull($logItem, 'log_items にログが記録されていること');
        $this->assertEquals(LogResourceActionType::USE->value, $logItem->action_type);
        $this->assertEquals($initialAmount, $logItem->before_amount);
        $this->assertEquals($initialAmount - 1, $logItem->after_amount);
        $this->assertEquals(LogResourceTriggerSource::USER_STAMINA_RECOVERY_COST->value, $logItem->trigger_source);
    }

    public function test_applyStaminaRecoveryFixed_複数個同時使用で回復する()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100;
        $currentStamina = 50;
        $fixedAmount = 30; // 固定30回復
        $amount = 3; // 3個使用

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryFixedItem',
            'type' => ItemType::STAMINA_RECOVERY_FIXED->value,
            'effect_value' => $fixedAmount,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryFixedItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise - 3個同時使用
        $this->itemStaminaRecoveryService->applyStaminaRecoveryFixed(
            $usrUserId,
            $mstItem,
            $amount,
            $now,
        );
        $this->saveAll();

        // Verify
        // 50 + (30 * 3) = 50 + 90 = 140
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(140, $usrUserParameter->getStamina());

        // アイテムが3個消費されたことを確認
        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_item_id', 'staminaRecoveryFixedItem')
            ->first();
        $this->assertEquals(7, $usrItem->getAmount());
    }

    public function test_applyStaminaRecoveryFixed_システム上限で満タン時はエラー()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100;
        $currentStamina = 999; // システム上限999で満タン

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryFixedItem',
            'type' => ItemType::STAMINA_RECOVERY_FIXED->value,
            'effect_value' => 30,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryFixedItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::USER_STAMINA_FULL);

        $this->itemStaminaRecoveryService->applyStaminaRecoveryFixed(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
    }

    public function test_applyStaminaRecoveryFixed_システム上限到達時は部分回復()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 300;
        $currentStamina = 980; // システム上限999に近い値
        $fixedAmount = 100; // 固定100回復

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryFixedItem',
            'type' => ItemType::STAMINA_RECOVERY_FIXED->value,
            'effect_value' => $fixedAmount,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryFixedItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        // システム上限999を超える場合は部分回復されて999になる
        $this->itemStaminaRecoveryService->applyStaminaRecoveryFixed(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
        $this->saveAll();

        // Verify
        // 980 + 100 → 999（81切り捨て、システム上限でカット）
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(999, $usrUserParameter->getStamina());
    }

    public function test_applyStaminaRecoveryFixed_部分回復時に複数個使用可能()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 300;
        $currentStamina = 800; // システム上限999に対して残り199
        $fixedAmount = 100; // 固定100回復

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryFixedItem',
            'type' => ItemType::STAMINA_RECOVERY_FIXED->value,
            'effect_value' => $fixedAmount,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryFixedItem',
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        // maxUsable = max(1, ceil((999-800)/100)) = max(1, ceil(1.99)) = 2
        // 2個使用可能で、実際の回復量は min(800 + 100*2, 999) - 800 = 199
        $this->itemStaminaRecoveryService->applyStaminaRecoveryFixed(
            $usrUserId,
            $mstItem,
            2, // 2個使用
            $now,
        );
        $this->saveAll();

        // Verify
        // 800 + 200 → 999（1切り捨て、システム上限でカット）
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(999, $usrUserParameter->getStamina());

        // アイテムが2個消費されている
        $usrItem = UsrItem::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals(8, $usrItem->getAmount());
    }

    public function test_applyStaminaRecoveryFixed_使用可能個数を超える場合はエラー()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 100;
        $currentStamina = 50;
        $fixedAmount = 30;

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItem = MstItem::factory()->create([
            'id' => 'staminaRecoveryFixedItem',
            'type' => ItemType::STAMINA_RECOVERY_FIXED->value,
            'effect_value' => $fixedAmount,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'staminaRecoveryFixedItem',
            'amount' => 100,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // 使用可能個数 = max(1, ceil((999 - 50) / 30)) = max(1, 32) = 32
        // amount=35 を指定すると使用可能個数(32)を超えるためエラー
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_PARAMETER);

        $this->itemStaminaRecoveryService->applyStaminaRecoveryFixed(
            $usrUserId,
            $mstItem,
            35, // 使用可能個数(32)を超えるためエラー
            $now,
        );
    }

    /**
     * 固定値回復アイテムの境界値テストデータ
     *
     * @return array<string, array{currentStamina: int, fixedAmount: int, expectedStamina: int}>
     */
    public static function params_test_applyStaminaRecoveryFixed_境界値テスト(): array
    {
        return [
            'スタミナ0 → ドリンク100使用 → 100回復' => [
                'currentStamina' => 0,
                'fixedAmount' => 100,
                'expectedStamina' => 100,
            ],
            'スタミナ899 → ドリンク100使用 → 100回復' => [
                'currentStamina' => 899,
                'fixedAmount' => 100,
                'expectedStamina' => 999,
            ],
            'スタミナ900 → ドリンク100使用 → 99回復（1切り捨て）' => [
                'currentStamina' => 900,
                'fixedAmount' => 100,
                'expectedStamina' => 999,
            ],
            'スタミナ950 → ドリンク100使用 → 49回復（51切り捨て）' => [
                'currentStamina' => 950,
                'fixedAmount' => 100,
                'expectedStamina' => 999,
            ],
            'スタミナ998 → ドリンク100使用 → 1回復（99切り捨て）' => [
                'currentStamina' => 998,
                'fixedAmount' => 100,
                'expectedStamina' => 999,
            ],
        ];
    }

    #[DataProvider('params_test_applyStaminaRecoveryFixed_境界値テスト')]
    public function test_applyStaminaRecoveryFixed_境界値テスト(
        int $currentStamina,
        int $fixedAmount,
        int $expectedStamina,
    ): void {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;
        $levelStamina = 300; // ユーザー上限

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItemId = 'staminaRecoveryFixedItem';
        $mstItem = MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::STAMINA_RECOVERY_FIXED->value,
            'effect_value' => $fixedAmount,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $this->itemStaminaRecoveryService->applyStaminaRecoveryFixed(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
        $this->saveAll();

        // Verify
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedStamina, $usrUserParameter->getStamina());
    }

    /**
     * 割合回復アイテムの境界値テストデータ
     *
     * @return array<string, array{currentStamina: int, levelStamina: int, percent: int, expectedStamina: int}>
     */
    public static function params_test_applyStaminaRecoveryPercent_境界値テスト(): array
    {
        // 50%回復 = 100回復として境界値をテスト
        return [
            'スタミナ0 → 50%回復（100）使用 → 100回復' => [
                'currentStamina' => 0,
                'levelStamina' => 200,
                'percent' => 50,
                'expectedStamina' => 100,
            ],
            'スタミナ899 → 50%回復（100）使用 → 100回復' => [
                'currentStamina' => 899,
                'levelStamina' => 200,
                'percent' => 50,
                'expectedStamina' => 999,
            ],
            'スタミナ900 → 50%回復（100）使用 → 99回復（1切り捨て）' => [
                'currentStamina' => 900,
                'levelStamina' => 200,
                'percent' => 50,
                'expectedStamina' => 999,
            ],
            'スタミナ950 → 50%回復（100）使用 → 49回復（51切り捨て）' => [
                'currentStamina' => 950,
                'levelStamina' => 200,
                'percent' => 50,
                'expectedStamina' => 999,
            ],
            'スタミナ998 → 50%回復（100）使用 → 1回復（99切り捨て）' => [
                'currentStamina' => 998,
                'levelStamina' => 200,
                'percent' => 50,
                'expectedStamina' => 999,
            ],
        ];
    }

    #[DataProvider('params_test_applyStaminaRecoveryPercent_境界値テスト')]
    public function test_applyStaminaRecoveryPercent_境界値テスト(
        int $currentStamina,
        int $levelStamina,
        int $percent,
        int $expectedStamina,
    ): void {
        // Setup
        $now = $this->fixTime();
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        $level = 10;

        $this->createStaminaRecoveryTestData($usrUserId, $level, $levelStamina);

        $mstItemId = 'staminaRecoveryPercentItem';
        $mstItem = MstItem::factory()->create([
            'id' => $mstItemId,
            'type' => ItemType::STAMINA_RECOVERY_PERCENT->value,
            'effect_value' => $percent,
        ])->toEntity();

        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $mstItemId,
            'amount' => 10,
        ]);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => $level,
            'exp' => 0,
            'coin' => 0,
            'stamina' => $currentStamina,
            'stamina_updated_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $this->itemStaminaRecoveryService->applyStaminaRecoveryPercent(
            $usrUserId,
            $mstItem,
            1,
            $now,
        );
        $this->saveAll();

        // Verify
        $usrUserParameter = UsrUserParameter::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($expectedStamina, $usrUserParameter->getStamina());
    }
}
