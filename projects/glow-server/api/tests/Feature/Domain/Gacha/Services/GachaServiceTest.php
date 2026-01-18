<?php

namespace Feature\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Gacha\Constants\GachaConstants;
use App\Domain\Gacha\Entities\GachaHistory;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUpper;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GachaServiceTest extends TestCase
{
    private GachaService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(GachaService::class);
    }

    public function testGenerateGachaProbability_ガシャ提供割合生成確認()
    {
        // Setup
        $this->createUsrUser();

        $upperGroup = 'upper_group';
        $prizeGroupId = 'prize_group_id';
        $fixedPrizeGroupId = 'fixed_prize_group_id';
        $oprGacha = OprGacha::factory()->create([
            'gacha_type' => GachaType::PICKUP->value,
            'upper_group' => $upperGroup,
            'multi_fixed_prize_count' => 1,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_group_id' => $fixedPrizeGroupId,
        ])->toEntity();
        OprGachaUpper::factory()->createMany([
            [
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::MAX_RARITY,
                'count' => 100,
            ],
            [
                'upper_group' => $upperGroup,
                'upper_type' => UpperType::PICKUP,
                'count' => 200,
            ]
        ]);
        $mstUnits = MstUnit::factory()->createMany([
            ['rarity' => RarityType::N->value],
            ['rarity' => RarityType::N->value],
            ['rarity' => RarityType::R->value],
            ['rarity' => RarityType::R->value],
            ['rarity' => RarityType::SR->value],
            ['rarity' => RarityType::SR->value],
            ['rarity' => RarityType::UR->value],
            ['rarity' => RarityType::UR->value],
        ])->map(fn ($mstUnit) => $mstUnit->toEntity());
        // 通常排出データ
        $mstUnits->each(function ($mstUnit) use ($prizeGroupId) {
            // N:60% R:25% SR:10% UR:5%とする
            $rarity = $mstUnit->getRarity();
            $weight = match ($rarity) {
                // weightの値が少数にならないよう合計値が100ではなく1000として各レアリティの重みを算出
                RarityType::N->value => 600 / 2,
                RarityType::R->value => 250 / 2,
                RarityType::SR->value => 100 / 2,
                RarityType::UR->value => 50 / 2,
            };
            OprGachaPrize::factory()->create([
                'group_id' => $prizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->getId(),
                'resource_amount' => 1,
                'weight' => $weight,
                'pickup' => $rarity === RarityType::UR->value,
            ]);
        });
        // 確定枠排出データ
        $mstUnits->filter(function ($mstUnit) {
            $rarity = $mstUnit->getRarity();
            return $rarity === RarityType::SR->value || $rarity === RarityType::UR->value;
        })->each(function ($mstUnit) use ($fixedPrizeGroupId) {
            $weight = match ($mstUnit->getRarity()) {
                RarityType::SR->value => 950 / 2,
                RarityType::UR->value => 50 / 2,
            };
            OprGachaPrize::factory()->create([
                'group_id' => $fixedPrizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->getId(),
                'resource_amount' => 1,
                'weight' => $weight
            ]);
        });

        // Exercise
        $actual = $this->service->generateGachaProbability($oprGacha->getId())->formatToResponse();

        // Verify
        $this->assertArrayHasKey('rarityProbabilities', $actual);
        $rarityProbabilities = $actual['rarityProbabilities'];
        $this->assertCount(4, $rarityProbabilities);
        foreach ($rarityProbabilities as $rarityProbability) {
            $probabilityMap = [
                RarityType::N->value => 60,
                RarityType::R->value => 25,
                RarityType::SR->value => 10,
                RarityType::UR->value => 5,
            ];
            $rarity = $rarityProbability['rarity'];
            $this->assertEquals($rarityProbability['probability'], $probabilityMap[$rarity]);
        }

        $this->assertArrayHasKey('probabilityGroups', $actual);
        $probabilityGroups = $actual['probabilityGroups'];
        $this->assertCount(4, $probabilityGroups);
        foreach ($probabilityGroups as $probabilityGroup) {
            $probabilityMap = [
                RarityType::N->value => 30,
                RarityType::R->value => 12.5,
                RarityType::SR->value => 5,
                RarityType::UR->value => 2.5,
            ];
            $rarity = $probabilityGroup['rarity'];
            $prizes = $probabilityGroup['prizes'];
            $this->assertCount(2, $prizes);
            foreach ($prizes as $prize) {
                $this->assertEquals($prize['probability'], $probabilityMap[$rarity]);
            }
        }

        $this->assertArrayHasKey('fixedProbabilities', $actual);
        $fixedProbabilities = $actual['fixedProbabilities'];
        $this->assertArrayHasKey('fixedCount', $fixedProbabilities);
        $this->assertEquals(1, $fixedProbabilities['fixedCount']);
        $probabilityGroups = $fixedProbabilities['probabilityGroups'];
        $this->assertCount(2, $probabilityGroups);
        foreach ($probabilityGroups as $probabilityGroup) {
            $probabilityMap = [
                RarityType::SR->value => 47.5,
                RarityType::UR->value => 2.5,
            ];
            $rarity = $probabilityGroup['rarity'];
            $prizes = $probabilityGroup['prizes'];
            $this->assertCount(2, $prizes);
            foreach ($prizes as $prize) {
                $this->assertEquals($prize['probability'], $probabilityMap[$rarity]);
            }
        }

        $this->assertArrayHasKey('upperProbabilities', $actual);
        $upperProbabilities = $actual['upperProbabilities'];
        $this->assertCount(2, $upperProbabilities);
        foreach ($upperProbabilities as $upperProbability) {
            $this->assertArrayHasKey('upperType', $upperProbability);
            $probabilityGroup = $upperProbability['probabilityGroups'];
            $this->assertCount(1, $probabilityGroup);
            $prizes = $probabilityGroup[0]['prizes'];
            $this->assertCount(2, $prizes);
            foreach ($prizes as $prize) {
                $this->assertEquals(50, $prize['probability']);
            }
        }
    }

    public static function params_test_validateCostType_ガシャタイプに対するコストタイプが適切かチェック()
    {
        $params = [];
        foreach (GachaType::cases() as $gachaType) {
            foreach (CostType::cases() as $costType) {
                $validCostTypes = GachaConstants::PERMISSION_GACHA_COST[$gachaType->value] ?? [];
                $isError = !in_array($costType->value, $validCostTypes);
                $params[] = [$gachaType, $costType, $isError, empty($validCostTypes)];
            }
        }

        return $params;
    }

    #[DataProvider('params_test_validateCostType_ガシャタイプに対するコストタイプが適切かチェック')]
    public function test_validateCostType_ガシャタイプに対するコストタイプが適切かチェック(
        GachaType $gachaType,
        CostType $costType,
        bool $isError,
        bool $isEmpty
    ) {
        if ($isEmpty) {
            $this->fail(
                sprintf('ガシャタイプ(%s)に対するコストタイプが設定されていません。', $gachaType->value)
            );
        }

        // Setup
        $oprGacha = OprGacha::factory()->create([
            'gacha_type' => $gachaType->value,
            'enable_ad_play' => 1,
        ])->toEntity();

        if ($isError) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::GACHA_TYPE_UNEXPECTED);
        }

        // Exercise
        $this->service->validateCostType($oprGacha, $costType);

        // Verify
        $this->assertTrue(true);
    }

    public static function params_testValidateExpiration_ガシャの有効期限検証()
    {
        return [
            '有効期限内' => [
                'now' => '2021-01-05 00:00:00',
                'expiresAt' => '2021-01-10 00:00:00',
                'errorCode' => null,
            ],
            '期限日時と現在時刻が同じ' => [
                'now' => '2021-01-10 00:00:00',
                'expiresAt' => '2021-01-10 00:00:00',
                'errorCode' => null,
            ],
            '期限切れ' => [
                'now' => '2021-01-10 00:00:01',
                'expiresAt' => '2021-01-10 00:00:00',
                'errorCode' => ErrorCode::GACHA_EXPIRED,
            ],
        ];
    }

    #[DataProvider('params_testValidateExpiration_ガシャの有効期限検証')]
    public function testValidateExpiration_ガシャの有効期限検証(string $now, string $expiresAt, ?int $errorCode)
    {
        // Setup
        $usrGacha = UsrGacha::factory()->create(['expires_at' => $expiresAt]);

        if ($errorCode) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }
        // Exercise
        $this->service->validateExpiration($usrGacha, CarbonImmutable::parse($now));

        // Verify
        $this->assertTrue(true);
    }

    public function testGetGachaHistories_期限内のガシャ履歴が取得できる()
    {
        // Setup
        $now = $this->fixTime();
        $usrUserId = 'user_1';
        $playedAtList = [
            // 履歴の日時が保持期限以上前なので除外される
            $now->subDays(GachaConstants::HISTORY_DAYS),
            $now->subDays(GachaConstants::HISTORY_DAYS)->subSecond(),
            // 履歴の日時が保持期限内なので含まれる
            $now->subDays(GachaConstants::HISTORY_DAYS)->addSecond(),
            $now->subDays(GachaConstants::HISTORY_DAYS - 1),
        ];
        $gachaHistories = collect();
        foreach ($playedAtList as $playedAt) {
            $gachaHistories->push(
                new GachaHistory(
                    'gacha_1',
                    'Diamond',
                    null,
                    300,
                    1,
                    $playedAt,
                    collect(),
                )
            );
        }
        $this->setToRedis(CacheKeyUtil::getGachaHistoryKey($usrUserId), $gachaHistories);

        // Exercise
        $actual = $this->service->getGachaHistories($usrUserId, $now);

        // Verify
        $this->assertCount(2, $actual);
    }
}
