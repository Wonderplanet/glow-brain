<?php

namespace Tests\Feature\Domain\Unit\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use App\Domain\Resource\Mst\Models\MstUnitSpecificRankUp;
use App\Domain\Unit\Enums\UnitColorType;
use App\Domain\Unit\Enums\UnitLabel;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Services\UnitRankUpService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UnitRankUpServiceTest extends TestCase
{
    private UnitRankUpService $unitRankUpService;

    public function setUp(): void
    {
        parent::setUp();
        $this->unitRankUpService = $this->app->make(UnitRankUpService::class);
    }

    public static function params_testRankUp_リミテッドメモリーの色分け通りにランクアップを正常に実行できる(): array
    {
        return [
            '無属性' => [UnitColorType::COLORLESS],
            '赤属性' => [UnitColorType::RED],
            '緑属性' => [UnitColorType::GREEN],
            '青属性' => [UnitColorType::BLUE],
            '黄属性' => [UnitColorType::YELLOW]
        ];
    }

    #[DataProvider("params_testRankUp_リミテッドメモリーの色分け通りにランクアップを正常に実行できる")]
    public function testRankUp_リミテッドメモリーの色分け通りにランクアップを正常に実行できる(UnitColorType $colorType)
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create([
            'color' => $colorType->value
        ])->toEntity();
        $mstItem = MstItem::factory()->create([
            'type' => ItemType::RANK_UP_MATERIAL->value,
            'effect_value' => $colorType->value
        ])->toEntity();
        $mstUnitRankUp = MstUnitRankUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'rank' => 1,
            'amount' => 1,
            'require_level' => 10,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'rank' => 0,
            'level' => $mstUnitRankUp->getRequireLevel(),
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => $mstUnitRankUp->getAmount(),
        ]);

        // Exercise
        $this->unitRankUpService->rankUp($usrUnit->getId(), $usrUser->getId(), CarbonImmutable::now());
        $this->saveAll();

        // ランクが更新されていること
        $usrUnit = UsrUnit::query()->where('id', $usrUnit->getId())->first();
        $this->assertEquals(1, $usrUnit->getRank());

        // コインが減っていること
        $usrItem = UsrItem::query()
            ->where('usr_user_id', $usrUser->getId())
            ->where('mst_item_id', $mstItem->getId())
            ->first();
        $this->assertEquals(0, $usrItem->getAmount());
    }

    public function testRankUp_複数種類のメモリーフラグメントを消費してランクアップを正常に実行できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // mst
        $color = UnitColorType::RED->value;
        $unitLabel = UnitLabel::DROP_SR->value;
        MstUnit::factory()->create(['id' => 'unit1', 'color' => $color, 'unit_label' => $unitLabel]);
        MstItem::factory()->createMany([
            ['id' => 'limitedMemoryRed', 'type' => ItemType::RANK_UP_MATERIAL->value, 'effect_value' => $color,],
            ['id' => 'memoryFragmentSR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::SR->value,],
            ['id' => 'memoryFragmentSSR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::SSR->value,],
            ['id' => 'memoryFragmentUR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::UR->value,],
        ]);
        MstUnitRankUp::factory()->create([
            'unit_label' => $unitLabel, 'rank' => 1, 'amount' => 1, 'require_level' => 10,
            'sr_memory_fragment_amount' => 5,
            'ssr_memory_fragment_amount' => 3,
            'ur_memory_fragment_amount' => 0,
        ]);

        // usr
        UsrUnit::factory()->create(['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'rank' => 0, 'level' => 10,]);
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'limitedMemoryRed', 'amount' => 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentSR', 'amount' => 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentSSR', 'amount' => 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentUR', 'amount' => 10,],
        ]);

        // Exercise
        $this->unitRankUpService->rankUp('usrUnit1', $usrUserId, $now);
        $this->saveAll();

        // ランクが更新されていること
        $usrUnit = UsrUnit::query()->where('id', 'usrUnit1')->first();
        $this->assertEquals(1, $usrUnit->getRank());

        // アイテム消費していること
        $usrItems = UsrItem::query()->where('usr_user_id', $usrUserId)->whereLike('mst_item_id', 'memoryFragment%')->get()->keyBy->getMstItemId();
        $this->assertCount(3, $usrItems);
        $this->assertEquals(5, $usrItems['memoryFragmentSR']->getAmount());
        $this->assertEquals(7, $usrItems['memoryFragmentSSR']->getAmount());
        $this->assertEquals(10, $usrItems['memoryFragmentUR']->getAmount());
    }

    public static function params_testRankUp_必要レベルに達していない場合エラーになる(): array
    {
        return [
            '無属性' => [UnitColorType::COLORLESS],
            '赤属性' => [UnitColorType::RED],
            '緑属性' => [UnitColorType::GREEN],
            '青属性' => [UnitColorType::BLUE],
            '黄属性' => [UnitColorType::YELLOW]
        ];
    }

    /**
     * @dataProvider params_testRankUp_必要レベルに達していない場合エラーになる
     */
    public function testRankUp_必要レベルに達していない場合エラーになる(UnitColorType $colorType)
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create([
            'color' => $colorType->value
        ])->toEntity();
        $mstItem = MstItem::factory()->create([
            'type' => ItemType::RANK_UP_MATERIAL->value,
            'effect_value' => $colorType->value
        ])->toEntity();
        $mstUnitRankUp = MstUnitRankUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'rank' => 1,
            'amount' => 1,
            'require_level' => 10,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'rank' => 0,
            'level' => $mstUnitRankUp->getRequireLevel() - 1,
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => $mstUnitRankUp->getAmount(),
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::UNIT_INSUFFICIENT_LEVEL);

        // Exercise
        $this->unitRankUpService->rankUp($usrUnit->getId(), $usrUser->getId(), CarbonImmutable::now());
    }

    public static function params_testRankUp_リミテッドメモリーの必要コストを所持していない場合エラーになる(): array
    {
        return [
            '無属性' => [UnitColorType::COLORLESS],
            '赤属性' => [UnitColorType::RED],
            '緑属性' => [UnitColorType::GREEN],
            '青属性' => [UnitColorType::BLUE],
            '黄属性' => [UnitColorType::YELLOW]
        ];
    }

    #[DataProvider("params_testRankUp_リミテッドメモリーの必要コストを所持していない場合エラーになる")]
    public function testRankUp_リミテッドメモリーの必要コストを所持していない場合エラーになる(UnitColorType $colorType)
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create([
            'color' => $colorType->value
        ])->toEntity();
        $mstItem = MstItem::factory()->create([
            'type' => ItemType::RANK_UP_MATERIAL->value,
            'effect_value' => $colorType->value
        ])->toEntity();
        $mstUnitRankUp = MstUnitRankUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'rank' => 1,
            'amount' => 10,
            'require_level' => 10,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'rank' => 0,
            'level' => $mstUnitRankUp->getRequireLevel(),
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => $mstUnitRankUp->getAmount() - 1,
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);

        // Exercise
        $this->unitRankUpService->rankUp($usrUnit->getId(), $usrUser->getId(), CarbonImmutable::now());
    }

    public static function params_testRankUp_メモリーフラグメントの必要コストを所持していない場合エラーになる(): array
    {
        return [
            'SR' => [RarityType::SR],
            'SSR' => [RarityType::SSR],
            'UR' => [RarityType::UR],
        ];
    }

    #[DataProvider("params_testRankUp_メモリーフラグメントの必要コストを所持していない場合エラーになる")]
    public function testRankUp_メモリーフラグメントの必要コストを所持していない場合エラーになる(?RarityType $rarity)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // error
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ITEM_AMOUNT_IS_NOT_ENOUGH);

        // mst
        $color = UnitColorType::RED->value;
        $unitLabel = UnitLabel::DROP_SR->value;
        MstUnit::factory()->create(['id' => 'unit1', 'color' => $color, 'unit_label' => $unitLabel]);
        MstItem::factory()->createMany([
            ['id' => 'limitedMemoryRed', 'type' => ItemType::RANK_UP_MATERIAL->value, 'effect_value' => $color,],
            ['id' => 'memoryFragmentSR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::SR->value,],
            ['id' => 'memoryFragmentSSR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::SSR->value,],
            ['id' => 'memoryFragmentUR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::UR->value,],
        ]);
        MstUnitRankUp::factory()->create([
            'unit_label' => $unitLabel, 'rank' => 1, 'amount' => 1, 'require_level' => 10,
            'sr_memory_fragment_amount' => $rarity === RarityType::SR ? 5 : 0,
            'ssr_memory_fragment_amount' => $rarity === RarityType::SSR ? 5 : 0,
            'ur_memory_fragment_amount' => $rarity === RarityType::UR ? 5 : 0,
        ]);

        // usr
        UsrUnit::factory()->create(['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'rank' => 0, 'level' => 10,]);
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'limitedMemoryRed', 'amount' => 10,],
            // テストケースの指定rarityのアイテムは未所持にして、それ以外は必要数揃っている状態にする
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentSR', 'amount' => $rarity === RarityType::SR ? 0 : 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentSSR', 'amount' => $rarity === RarityType::SSR ? 0 : 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentUR', 'amount' => $rarity === RarityType::UR ? 0 : 10,],
        ]);

        // Exercise
        $this->unitRankUpService->rankUp('usrUnit1', $usrUserId, $now);

        // Verify
    }

    public static function params_testRankUp_上限を超えてランクアップしようとするとエラーになる(): array
    {
        return [
            '無属性' => [UnitColorType::COLORLESS],
            '赤属性' => [UnitColorType::RED],
            '緑属性' => [UnitColorType::GREEN],
            '青属性' => [UnitColorType::BLUE],
            '黄属性' => [UnitColorType::YELLOW]
        ];
    }

    /**
     * @dataProvider params_testRankUp_上限を超えてランクアップしようとするとエラーになる
     */
    public function testRankUp_上限を超えてランクアップしようとするとエラーになる(UnitColorType $colorType)
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create([
            'color' => $colorType->value
        ])->toEntity();
        $mstItem = MstItem::factory()->create([
            'type' => ItemType::RANK_UP_MATERIAL->value,
            'effect_value' => $colorType->value
        ])->toEntity();
        $mstUnitRankUp = MstUnitRankUp::factory()->create([
            'unit_label' => $mstUnit->getUnitLabel(),
            'rank' => 1,
            'amount' => 1,
            'require_level' => 10,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'rank' => 1,
            'level' => $mstUnitRankUp->getRequireLevel(),
        ]);
        UsrItem::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_item_id' => $mstItem->getId(),
            'amount' => $mstUnitRankUp->getAmount(),
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Exercise
        $this->unitRankUpService->rankUp($usrUnit->getId(), $usrUser->getId(), CarbonImmutable::now());
    }

    public function test_rankUp_キャラ個別設定でランクアップできる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // mst
        $color = UnitColorType::RED->value;
        $unitLabel = UnitLabel::DROP_SR->value;
        MstUnit::factory()->create([
            'id' => 'unit1', 'color' => $color, 'unit_label' => $unitLabel,
            'has_specific_rank_up' => 1,
        ]);
        MstItem::factory()->createMany([
            ['id' => 'limitedMemoryRed', 'type' => ItemType::RANK_UP_MATERIAL->value, 'effect_value' => $color,],
            ['id' => 'memoryFragmentSR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::SR->value,],
            ['id' => 'memoryFragmentSSR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::SSR->value,],
            ['id' => 'memoryFragmentUR', 'type' => ItemType::RANK_UP_MEMORY_FRAGMENT->value, 'rarity' => RarityType::UR->value,],
            ['id' => 'unitMemoryUni1', 'type' => ItemType::RANK_UP_MATERIAL->value, 'effect_value' => 'unit1',],
        ]);
        // 今回は使わないが用意
        MstUnitRankUp::factory()->create([
            'unit_label' => $unitLabel, 'rank' => 1, 'amount' => 1, 'require_level' => 10,
            // 未使用想定だが、誤って使った場合に、リソース不足エラーになるように設定
            'sr_memory_fragment_amount' => 999,
            'ssr_memory_fragment_amount' => 999,
            'ur_memory_fragment_amount' => 999,
        ]);
        // キャラ個別設定
        MstUnitSpecificRankUp::factory()->create([
            'mst_unit_id' => 'unit1', 'rank' => 1, 'amount' => 1, 'require_level' => 10,
            'unit_memory_amount' => 5,
            'sr_memory_fragment_amount' => 5,
            'ssr_memory_fragment_amount' => 0,
            'ur_memory_fragment_amount' => 0,
        ]);

        // usr
        UsrUnit::factory()->create(['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'rank' => 0, 'level' => 10,]);
        UsrItem::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'limitedMemoryRed', 'amount' => 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentSR', 'amount' => 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentSSR', 'amount' => 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'memoryFragmentUR', 'amount' => 10,],
            ['usr_user_id' => $usrUserId, 'mst_item_id' => 'unitMemoryUni1', 'amount' => 10,],
        ]);

        // Exercise
        $this->unitRankUpService->rankUp('usrUnit1', $usrUserId, $now);
        $this->saveAll();

        // ランクが更新されていること
        $usrUnit = UsrUnit::query()->where('id', 'usrUnit1')->first();
        $this->assertEquals(1, $usrUnit->getRank());

        // キャラ個別設定の方で、アイテム消費していること
        $usrItems = UsrItem::query()->where('usr_user_id', $usrUserId)
            ->whereLike('mst_item_id', 'memoryFragment%')
            ->orWhereLike('mst_item_id', 'unitMemory%')
            ->get()->keyBy->getMstItemId();
        $this->assertCount(4, $usrItems);
        $this->assertEquals(10 - 5, $usrItems['memoryFragmentSR']->getAmount());
        $this->assertEquals(10, $usrItems['memoryFragmentSSR']->getAmount());
        $this->assertEquals(10, $usrItems['memoryFragmentUR']->getAmount());
        $this->assertEquals(10 - 5, $usrItems['unitMemoryUni1']->getAmount());
    }
}
