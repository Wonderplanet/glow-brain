<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use Tests\Support\Traits\TestLogTrait;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\UseCases\GachaDrawUseCase;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Domain\Unit\Constants\UnitConstant;
use App\Domain\Unit\Models\LogUnit;
use Tests\Support\Entities\CurrentUser;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

class LogUnitTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_GachaDrawUseCase_exec_排出されたユニットのログが保存できている()
    {
        // 必要なマスタデータ作成
        $fragmentMstItemId = 'fragment1';
        MstItem::factory()->create(['id' => $fragmentMstItemId]);
        $unit = MstUnit::factory()->create([
            'fragment_mst_item_id' => $fragmentMstItemId,
            'rarity' => RarityType::SR->value,
        ])->toEntity();
        // OprGachaI18nを追加
        OprGachaI18n::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'language' => \App\Domain\Common\Enums\Language::Ja->value,
        ]);
        OprGachaPrize::factory()->create([
            'group_id' => 'prize_group_id',
            'resource_type' => RewardType::UNIT,
            'resource_id' => $unit->getId(),
        ]);
        OprGacha::factory()->create([
            'id' => 'opr_gacha_id',
            'gacha_type' => GachaType::PREMIUM->value,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prize_group_id',
        ]);
        OprGachaUseResource::factory()->create([
            'opr_gacha_id' => 'opr_gacha_id',
            'cost_type' => CostType::DIAMOND,
            'cost_id' => null,
            'cost_num' => 300,
            'draw_count' => 1,
            'cost_priority' => 1,
        ]);

        // ユーザデータ作成
        $user = $this->createUsrUser();
        UsrUserParameter::factory()->create(['usr_user_id' => $user->getId()]);
        UsrCurrencySummary::factory()->create(['usr_user_id' => $user->getId()]);
        $this->createDiamond($user->getId(), 1000, 0, 0);

        // GachaDrawUseCase実行
        $useCase = $this->app->make(GachaDrawUseCase::class);
        $result = $useCase->exec(
            new CurrentUser($user->getId()),
            'opr_gacha_id',
            0,
            1,
            null,
            300,
            UserConstant::PLATFORM_IOS,
            CurrencyConstants::PLATFORM_APPSTORE,
            CostType::DIAMOND
        );

        // log_unitsテーブルに1件記録されていること
        $this->assertDatabaseCount('log_units', 1);
        $log = LogUnit::query()->first();
        $this->assertEquals($user->getId(), $log->usr_user_id); // プロパティ名で直接比較
        $this->assertEquals($unit->getId(), $log->mst_unit_id);
        $this->assertEquals(UnitConstant::FIRST_UNIT_LEVEL, $log->level);
        $this->assertEquals(UnitConstant::FIRST_UNIT_RANK, $log->rank);
        $this->assertEquals(UnitConstant::FIRST_UNIT_GRADE_LEVEL, $log->grade_level);
    }
}
