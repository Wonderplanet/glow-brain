<?php

declare(strict_types=1);

namespace Tests\Feature\Scenario;

use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\Item\Models\Eloquent\UsrItem;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUpper;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Domain\Resource\Mst\Models\OprStepupGacha;
use App\Domain\Resource\Mst\Models\OprStepupGachaStep;
use App\Domain\User\Models\UsrUserParameter;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use Tests\Support\Traits\TestMultipleApiRequestsTrait;

class GachaDrawPrizeTypeScenarioTest extends BaseControllerTestCase
{
    use TestMultipleApiRequestsTrait;

    protected string $baseUrl = '/api/gacha/';

    public function setUp(): void
    {
        parent::setUp();
        $this->createMasterRelease();
    }

    /**
     * スタンダードガチャ（draw/diamond）10連で
     * GachaPrizeType の全4種類（REGULAR / FIXED / MAX_RARITY / PICKUP）が
     * レスポンスに含まれることを確認する
     *
     * 天井設計:
     *   PICKUP_CEILING=6  / UsrGachaUpper(PICKUP).count=5  → draw1 で発動・全リセット
     *   MAX_RARITY_CEILING=5 / UsrGachaUpper(MAX_RARITY).count=0 → draw6 で発動・全リセット
     *   multi_fixed_prize_count=1 → draw10 が FIXED
     *
     * 期待順序: [PICKUP, REGULAR, REGULAR, REGULAR, REGULAR, MAX_RARITY, REGULAR, REGULAR, REGULAR, FIXED]
     */
    public function test_スタンダードガチャ10連でPRIZE_TYPE全4種類がレスポンスされること(): void
    {
        // Setup
        $usrUserId    = $this->usrUserId;
        $oprGachaId   = 'std_gacha_1';
        $prizeGroupId = 'std_prize_group_1';
        $fixedGroupId = 'std_fixed_group_1';
        $upperGroup   = 'std_upper_group_1';

        // ユーザー
        $this->createUsrUser();
        $this->createDiamond($usrUserId, 100);
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);

        // ユニット
        // Unit A: UR + pickup=true → PICKUP/MAX_RARITY 天井どちらが発動しても全カウンタリセット
        $unitA = MstUnit::factory()->create(['rarity' => RarityType::UR->value]);
        // Unit B: SR, pickup=false → 通常draw用（weight大でほぼ確実に選ばれる）
        $unitB = MstUnit::factory()->create(['rarity' => RarityType::SR->value]);
        // Unit C: SR → 固定枠用
        $unitC = MstUnit::factory()->create(['rarity' => RarityType::SR->value]);

        // 天井マスター
        OprGachaUpper::factory()->create([
            'upper_group' => $upperGroup,
            'upper_type'  => UpperType::PICKUP->value,
            'count'       => 6,
        ]);
        OprGachaUpper::factory()->create([
            'upper_group' => $upperGroup,
            'upper_type'  => UpperType::MAX_RARITY->value,
            'count'       => 5,
        ]);

        // ガチャマスター
        OprGacha::factory()->create([
            'id'                      => $oprGachaId,
            'gacha_type'              => GachaType::PREMIUM->value,
            'upper_group'             => $upperGroup,
            'prize_group_id'          => $prizeGroupId,
            'fixed_prize_group_id'    => $fixedGroupId,
            'multi_draw_count'        => 10,
            'multi_fixed_prize_count' => 1,
        ]);
        OprGachaI18n::factory()->create(['opr_gacha_id' => $oprGachaId]);

        // 賞品（レギュラー枠 - pickup=true UR）: PICKUP/MAX_RARITY天井発動時に選ばれる
        OprGachaPrize::factory()->create([
            'group_id'      => $prizeGroupId,
            'resource_type' => RewardType::UNIT->value,
            'resource_id'   => $unitA->id,
            'weight'        => 1,       // weight小 → 通常drawでは選ばれにくい
            'pickup'        => true,
        ]);
        // 賞品（レギュラー枠 - pickup=false SR）: 通常drawで確実に選ばれる
        OprGachaPrize::factory()->create([
            'group_id'      => $prizeGroupId,
            'resource_type' => RewardType::UNIT->value,
            'resource_id'   => $unitB->id,
            'weight'        => 999999,  // weight大 → 通常drawでほぼ確実に選ばれる
            'pickup'        => false,
        ]);

        // 賞品（固定枠）: SR
        OprGachaPrize::factory()->create([
            'group_id'      => $fixedGroupId,
            'resource_type' => RewardType::UNIT->value,
            'resource_id'   => $unitC->id,
            'weight'        => 100,
            'pickup'        => false,
        ]);

        // 消費リソース: ダイヤ10個で10連
        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'cost_type'    => CostType::DIAMOND->value,
            'cost_num'     => 10,
            'draw_count'   => 10,
        ]);

        // ユーザー天井カウンタ
        // PICKUP: count=5 → draw1 で 5+1=6(天井) 発動
        UsrGachaUpper::factory()->create([
            'usr_user_id' => $usrUserId,
            'upper_group' => $upperGroup,
            'upper_type'  => UpperType::PICKUP->value,
            'count'       => 5,
        ]);
        // MAX_RARITY: count=0 → 全リセット後 5 連続で発動（draw6）
        UsrGachaUpper::factory()->create([
            'usr_user_id' => $usrUserId,
            'upper_group' => $upperGroup,
            'upper_type'  => UpperType::MAX_RARITY->value,
            'count'       => 0,
        ]);

        // Exercise
        $response = $this->sendRequest('draw/diamond', [
            'oprGachaId' => $oprGachaId,
            'drewCount'  => 0,
            'playNum'    => 10,
            'costNum'    => 10,
        ]);

        // Verify
        $response->assertStatus(200);
        $gachaResults = $response->json()['gachaResults'];
        $this->assertCount(10, $gachaResults);

        $prizeTypes = collect($gachaResults)->pluck('prizeType')->toArray();

        // 期待される抽選順序を確認する
        // draw1: PICKUP (5+1=6 → 天井発動・全リセット)
        // draw2-5: REGULAR (カウンタ蓄積中)
        // draw6: MAX_RARITY (0+5=5 → 天井発動・全リセット)
        // draw7-9: REGULAR (カウンタ蓄積中)
        // draw10: FIXED (regularDrawCount=9 を超えた確定枠)
        $this->assertEquals([
            GachaPrizeType::PICKUP->value,
            GachaPrizeType::REGULAR->value,
            GachaPrizeType::REGULAR->value,
            GachaPrizeType::REGULAR->value,
            GachaPrizeType::REGULAR->value,
            GachaPrizeType::MAX_RARITY->value,
            GachaPrizeType::REGULAR->value,
            GachaPrizeType::REGULAR->value,
            GachaPrizeType::REGULAR->value,
            GachaPrizeType::FIXED->value,
        ], $prizeTypes, '抽選順序が期待通りであること');
    }

    /**
     * ステップアップガチャ（draw/item）10連で
     * REGULAR と FIXED がレスポンスに含まれることを確認する
     *
     * StepUpGachaDrawService は天井なし固定（$oprGachaUppers = collect() 固定）のため
     * PICKUP / MAX_RARITY は含まれない
     *
     * 期待構成: 9× REGULAR + 1× FIXED (draw10)
     */
    public function test_ステップアップガチャ10連でREGULARとFIXEDがレスポンスされること(): void
    {
        // Setup
        $usrUserId    = $this->usrUserId;
        $oprGachaId   = 'step_gacha_1';
        $prizeGroupId = 'step_prize_group_1';
        $fixedGroupId = 'step_fixed_group_1';

        // ユーザー
        $this->createUsrUser();
        $this->createDiamond($usrUserId);
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);

        // ユニット
        $unitC = MstUnit::factory()->create(['rarity' => RarityType::SR->value]);
        $unitD = MstUnit::factory()->create(['rarity' => RarityType::R->value]);

        // コストアイテム
        $costItem   = MstItem::factory()->create();
        $costItemId = $costItem->id;

        // ガチャマスター
        OprGacha::factory()->create([
            'id'                   => $oprGachaId,
            'gacha_type'           => GachaType::STEPUP->value,
            'prize_group_id'       => $prizeGroupId,
            'fixed_prize_group_id' => $fixedGroupId,
        ]);
        OprGachaI18n::factory()->create(['opr_gacha_id' => $oprGachaId]);

        OprStepupGacha::factory()->create([
            'opr_gacha_id'    => $oprGachaId,
            'max_step_number' => 1,
            'max_loop_count'  => 1,
        ]);

        // ステップ設定: 10連 / 固定1枠
        OprStepupGachaStep::factory()->create([
            'opr_gacha_id'         => $oprGachaId,
            'step_number'          => 1,
            'cost_type'            => CostType::ITEM->value,
            'cost_id'              => $costItemId,
            'cost_num'             => 1,
            'draw_count'           => 10,
            'fixed_prize_count'    => 1,
            'prize_group_id'       => $prizeGroupId,
            'fixed_prize_group_id' => $fixedGroupId,
            'is_first_free'        => false,
        ]);

        // 賞品（レギュラー枠）
        OprGachaPrize::factory()->create([
            'group_id'      => $prizeGroupId,
            'resource_type' => RewardType::UNIT->value,
            'resource_id'   => $unitC->id,
            'weight'        => 100,
            'pickup'        => false,
        ]);

        // 賞品（固定枠）
        OprGachaPrize::factory()->create([
            'group_id'      => $fixedGroupId,
            'resource_type' => RewardType::UNIT->value,
            'resource_id'   => $unitD->id,
            'weight'        => 100,
            'pickup'        => false,
        ]);

        // ユーザーアイテム（コスト支払い用）
        UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => $costItemId,
            'amount'      => 1,
        ]);

        // ユーザーガチャ（ステップ進捗）
        UsrGacha::factory()->create([
            'usr_user_id'         => $usrUserId,
            'opr_gacha_id'        => $oprGachaId,
            'current_step_number' => 1,
        ]);

        // Exercise
        $response = $this->sendRequest('draw/item', [
            'oprGachaId'        => $oprGachaId,
            'drewCount'         => 0,
            'playNum'           => 10,
            'costId'            => $costItemId,
            'costNum'           => 1,
            'currentStepNumber' => 1,
        ]);

        // Verify
        $response->assertStatus(200);
        $gachaResults = $response->json()['gachaResults'];
        $this->assertCount(10, $gachaResults);

        $prizeTypes = collect($gachaResults)->pluck('prizeType')->toArray();

        $this->assertNotContains(GachaPrizeType::PICKUP->value,     $prizeTypes, 'PICKUP は含まれないこと（天井なし）');
        $this->assertNotContains(GachaPrizeType::MAX_RARITY->value, $prizeTypes, 'MAX_RARITY は含まれないこと（天井なし）');

        // REGULAR が9個・FIXED が1個（draw10）であることを確認する
        $regularCount = collect($prizeTypes)->filter(fn($t) => $t === GachaPrizeType::REGULAR->value)->count();
        $fixedCount   = collect($prizeTypes)->filter(fn($t) => $t === GachaPrizeType::FIXED->value)->count();
        $this->assertSame(9, $regularCount, 'REGULAR が9個であること');
        $this->assertSame(1, $fixedCount,   'FIXED が1個（draw10）であること');
    }
}
