<?php

namespace Tests\Feature\Log;

use App\Domain\Common\Enums\Language;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\LogGacha;
use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaI18n;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUpper;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;
use Tests\Feature\Http\Controllers\BaseControllerTestCase;
use Tests\Support\Traits\TestLogTrait;

class LogGachaTest extends BaseControllerTestCase
{
    use TestLogTrait;

    protected string $baseUrl = '/api/gacha/';

    public function setUp(): void
    {
        parent::setUp();
    }

    private function createTestData(string $oprGachaId): string
    {
        $fragmentMstItemId = 'fragment1';
        MstItem::factory()->create(['id' => $fragmentMstItemId]);

        $mstUnits = MstUnit::factory(9)
            ->create(['fragment_mst_item_id' => $fragmentMstItemId])
            ->map(fn ($mstUnit) => $mstUnit->toEntity());
        $mstUnits->add(
            MstUnit::factory()
                ->create(['fragment_mst_item_id' => $fragmentMstItemId, 'rarity' => RarityType::UR->value])
                ->toEntity()
        );

        OprGachaI18n::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'language' => Language::Ja->value,
        ]);

        $gachaPrizeList = [];
        foreach ($mstUnits as $mstUnit) {
            $gachaPrizeList[] = [
                'group_id' => 'prize_group_id',
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->getId(),
                'pickup' => $mstUnit->getRarity() === RarityType::SSR->value ? 1 : 0,
            ];
        }
        OprGachaPrize::factory()->createMany($gachaPrizeList);

        $upperGroup = 'Premium';
        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::PREMIUM->value,
            'upper_group' => $upperGroup,
            'multi_draw_count' => 10,
            'prize_group_id' => 'prize_group_id',
        ]);
        OprGachaUpper::factory()->create([
            'upper_group' => $upperGroup,
            'upper_type' => UpperType::MAX_RARITY->value,
            'count' => 100,
        ]);

        OprGachaUseResource::factory()->createMany([
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 100,
                'draw_count' => 1,
                'cost_priority' => 2,
            ],
            [
                'opr_gacha_id' => 'opr_gacha_id',
                'cost_type' => CostType::DIAMOND,
                'cost_id' => null,
                'cost_num' => 1000,
                'draw_count' => 10,
                'cost_priority' => 1,
            ],
        ]);
        return $upperGroup;
    }

    public function test_draw_ガチャを引きガチャログが保存される()
    {
        // Setup
        $this->fixTime('2023-01-01 00:00:00');
        $nginxRequestId = __FUNCTION__;
        $this->setNginxRequestId($nginxRequestId);

        $usrUserId = $this->createUsrUser()->getId();

        $oprGachaId = 'opr_gacha_id';
        $upperGroup = $this->createTestData($oprGachaId);
        $this->createDiamond($usrUserId, 1000);

        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'coin' => 1000,
        ]);
        UsrGachaUpper::factory()->create([
            'usr_user_id' => $usrUserId,
            'upper_group' => $upperGroup,
            'upper_type' => UpperType::MAX_RARITY,
            'count' => 99,
        ]);

        // Exercise
        $drawCount = 10;
        $requestData = [
            'oprGachaId' => $oprGachaId,
            'drewCount' => 0,
            'playNum' => $drawCount,
            'costNum' => 1000,
        ];
        $response = $this->sendRequest('draw/diamond', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        /** @var LogGacha $logGacha */
        $logGacha = LogGacha::query()
            ->where('usr_user_id', $usrUserId)
            ->where('nginx_request_id', $nginxRequestId)
            ->get()
            ->first();
        $this->assertNotNull($logGacha);

        $this->assertEquals($oprGachaId, $logGacha->getOprGachaId());
        $this->assertCount($drawCount, $logGacha->getResult());
        $this->assertEquals(CostType::DIAMOND->value, $logGacha->getCostType());
        $this->assertEquals($drawCount, $logGacha->getDrawCount());
    }
}
