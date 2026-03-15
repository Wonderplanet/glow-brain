<?php

declare(strict_types=1);

namespace Tests\Feature\Scenario;

use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstTutorial;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Tutorial\Models\UsrTutorialGacha;
use App\Domain\Unit\Enums\UnitLabel;
use App\Domain\User\Models\UsrUserParameter;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use Tests\Support\Traits\TestMultipleApiRequestsTrait;

class TutorialGachaPrizeTypeScenarioTest extends BaseControllerTestCase
{
    use TestMultipleApiRequestsTrait;

    protected string $baseUrl = '/api/tutorial/';

    public function setUp(): void
    {
        parent::setUp();
        $this->createMasterRelease();
    }

    /**
     * チュートリアルガシャ10連でprizeTypeがdrawとconfirmのレスポンスに正しく反映されることを確認する
     *
     * 設計:
     *   multi_draw_count=10, multi_fixed_prize_count=1, upper_group='None'（天井なし）
     *   通常枠: $unitA (SR, weight大) → draw1〜9 でほぼ確実に選ばれる
     *   確定枠: $unitB (SR, fixed_prize_group) → draw10 が FIXED
     *
     * 期待: [REGULAR×9, FIXED×1]
     */
    public function test_チュートリアルガシャ10連でprizeTypeがdrawとconfirmのレスポンスに正しく反映されること(): void
    {
        // Setup
        $usrUserId = $this->usrUserId;
        $prizeGroupId = 'tutorial_prize_group_1';
        $fixedGroupId = 'tutorial_fixed_group_1';

        // ユーザーデータ
        // tutorial_status は confirm 時の TutorialStatusService で参照される
        $this->createUsrUser(['tutorial_status' => 'beforeGachaConfirmed']);
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);
        $this->createDiamond($usrUserId);

        // ユニット（通常枠用SR・確定枠用SR）
        $unitA = MstUnit::factory()->create([
            'unit_label' => UnitLabel::DROP_SR->value,
            'fragment_mst_item_id' => 'fragment_unit_a',
        ]);
        $unitB = MstUnit::factory()->create([
            'unit_label' => UnitLabel::DROP_SR->value,
            'fragment_mst_item_id' => 'fragment_unit_b',
        ]);

        // MstItem（重複ユニットがかけらアイテムへ変換される際に参照される）
        MstItem::factory()->createMany([
            ['id' => 'fragment_unit_a'],
            ['id' => 'fragment_unit_b'],
        ]);

        // キャラのかけら変換設定（SR用）
        MstUnitFragmentConvert::factory()->create([
            'unit_label' => UnitLabel::DROP_SR->value,
            'convert_amount' => 10,
        ]);

        // ガチャマスター（チュートリアル）
        OprGacha::factory()->create([
            'id' => 'tutorial_gacha_1',
            'gacha_type' => GachaType::TUTORIAL->value,
            'upper_group' => 'None',
            'multi_draw_count' => 10,
            'multi_fixed_prize_count' => 1,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_group_id' => $fixedGroupId,
        ]);

        // 賞品（通常枠: weight大でほぼ確実に選ばれる）
        OprGachaPrize::factory()->create([
            'group_id' => $prizeGroupId,
            'resource_type' => RewardType::UNIT->value,
            'resource_id' => $unitA->id,
            'weight' => 999999,
            'pickup' => false,
        ]);

        // 賞品（確定枠）
        OprGachaPrize::factory()->create([
            'group_id' => $fixedGroupId,
            'resource_type' => RewardType::UNIT->value,
            'resource_id' => $unitB->id,
            'weight' => 100,
            'pickup' => false,
        ]);

        // MstTutorial（TutorialStatusService が参照: NEW_GACHA_CONFIRMED および START_MAIN_PART3 を含む行が必要）
        MstTutorial::factory()->createMany([
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 1,
                'function_name' => 'beforeGachaConfirmed',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 2,
                'function_name' => TutorialFunctionName::GACHA_CONFIRMED->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 3,
                'function_name' => TutorialFunctionName::NEW_GACHA_CONFIRMED->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 4,
                'function_name' => 'afterGachaConfirmed',
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
            [
                'type' => TutorialType::MAIN,
                'sort_order' => 5,
                'function_name' => TutorialFunctionName::START_MAIN_PART3->value,
                'start_at' => '2020-01-01 00:00:00',
                'end_at' => '2037-01-01 00:00:00',
            ],
        ]);

        // Step 1: draw API
        $drawResponse = $this->sendRequest('gacha_draw', []);
        $drawResponse->assertStatus(200);

        // draw レスポンス確認
        $gachaResults = $drawResponse->json()['gachaResults'];
        $this->assertCount(10, $gachaResults, 'gachaResults が10件であること');

        // 各要素に prizeType フィールドが存在すること
        foreach ($gachaResults as $result) {
            $this->assertArrayHasKey('prizeType', $result);
        }

        // Regular が含まれること（通常枠 draw1〜9）
        $prizeTypes = collect($gachaResults)->pluck('prizeType')->toArray();
        $this->assertContains(GachaPrizeType::REGULAR->value, $prizeTypes, 'prizeType に Regular が含まれること');

        // 最後の1件の prizeType が Fixed であること（multi_fixed_prize_count=1 の確定枠 draw10）
        $this->assertEquals(
            GachaPrizeType::FIXED->value,
            $gachaResults[9]['prizeType'],
            '最後の1件の prizeType が Fixed であること'
        );

        // DB確認: gacha_result_json の prize_types に Fixed が含まれること（drawで保存されたJSON）
        $usrTutorialGacha = UsrTutorialGacha::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrTutorialGacha);
        $gachaResultJson = json_decode($usrTutorialGacha->gacha_result_json, true);
        $this->assertContains(
            GachaPrizeType::FIXED->value,
            $gachaResultJson['prize_types'],
            'gacha_result_json の prize_types に Fixed が含まれること'
        );

        // Step 2: confirm API
        $this->resetAppForNextRequest($this->usrUserId);

        $confirmResponse = $this->sendRequest('gacha_confirm', []);
        $confirmResponse->assertStatus(200);

        // confirm レスポンス確認
        $confirmGachaResults = $confirmResponse->json()['gachaResults'];
        $this->assertNotEmpty($confirmGachaResults, 'gachaResults が存在すること');

        // prizeType に Fixed が含まれること
        $confirmPrizeTypes = collect($confirmGachaResults)->pluck('prizeType')->toArray();
        $this->assertContains(
            GachaPrizeType::FIXED->value,
            $confirmPrizeTypes,
            'confirm レスポンスの prizeType に Fixed が含まれること'
        );
    }
}
