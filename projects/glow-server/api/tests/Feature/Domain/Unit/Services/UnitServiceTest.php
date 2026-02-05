<?php

namespace Tests\Feature\Domain\Unit\Services;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitFragmentConvert;
use App\Domain\Unit\Enums\UnitLabel;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Services\UnitService;
use Tests\Feature\Domain\Reward\Test1Reward;
use Tests\TestCase;

class UnitServiceTest extends TestCase
{
    private UnitService $unitService;

    public function setUp(): void
    {
        parent::setUp();
        $this->unitService = $this->app->make(UnitService::class);
    }

    public function test_convertDuplicatedUnitToItem_重複所持しているユニット報酬を別リソースへ変換できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getUsrUserId();

        // mst
        MstUnit::factory()->createMany([
            ['id' => 'unit1', 'unit_label' => UnitLabel::DROP_R, 'fragment_mst_item_id' => 'charaFragment1',],
            ['id' => 'unit2', 'unit_label' => UnitLabel::DROP_SR, 'fragment_mst_item_id' => 'charaFragment2',],
            ['id' => 'unit3', 'unit_label' => UnitLabel::DROP_SSR, 'fragment_mst_item_id' => 'charaFragment3',],
            ['id' => 'unit4', 'unit_label' => UnitLabel::DROP_UR, 'fragment_mst_item_id' => 'charaFragment4',],
            ['id' => 'unit5', 'unit_label' => UnitLabel::DROP_UR, 'fragment_mst_item_id' => 'charaFragment5',],
        ]);
        MstUnitFragmentConvert::factory()->createMany([
            ['unit_label' => UnitLabel::DROP_R, 'convert_amount' => 2],
            ['unit_label' => UnitLabel::DROP_SR, 'convert_amount' => 3],
            ['unit_label' => UnitLabel::DROP_SSR, 'convert_amount' => 4],
            ['unit_label' => UnitLabel::DROP_UR, 'convert_amount' => 5],
        ]);
        MstItem::factory()->createMany([
            ['id' => 'charaFragment1'],
            ['id' => 'charaFragment2'],
            ['id' => 'charaFragment3'],
            ['id' => 'charaFragment4'],
            ['id' => 'charaFragment5'],
            ['id' => 'item1'],
            ['id' => 'item2'],
        ]);

        // usr
        UsrUnit::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit3'],
            ['usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit4'],
        ]);

        $rewards = collect([
            // unit
            new Test1Reward(RewardType::UNIT, 'unit1', 1),
            new Test1Reward(RewardType::UNIT, 'unit2', 2),
            new Test1Reward(RewardType::UNIT, 'unit3', 3),
            new Test1Reward(RewardType::UNIT, 'unit4', 4),
            //   同じunit5報酬を複数のRewardインスタンスに分けて設定
            new Test1Reward(RewardType::UNIT, 'unit5', 1),
            new Test1Reward(RewardType::UNIT, 'unit5', 1),
            new Test1Reward(RewardType::UNIT, 'unit5', 3),
            // unit以外の変換されないリソース
            new Test1Reward(RewardType::ITEM, 'item1', 1),
            new Test1Reward(RewardType::ITEM, 'item2', 2),
        ])->keyBy->getId();

        // Exercise
        $this->unitService->convertDuplicatedUnitToItem($usrUserId, $rewards);

        // Verify
        $this->assertCount(10, $rewards);

        $this->assertEqualsCanonicalizing([
            // 初獲得のユニット
            'unit1-1',
            'unit2-1',
            'unit5-1',
            // 重複獲得のユニットはアイテムへ変換されている
            'charaFragment2-' . (2 - 1) * 3,
            'charaFragment3-' . 3 * 4,
            'charaFragment4-' . 4 * 5,
            'charaFragment5-' . 1 * 5,
            'charaFragment5-' . 3 * 5,
            // unit以外の変換されないリソースはそのまま
            'item1-1',
            'item2-2',
        ], $rewards->map(function (Test1Reward $reward) {
            return $reward->getResourceId() . '-' . $reward->getAmount();
        })->values()->toArray());
    }
}
