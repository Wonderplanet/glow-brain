<?php

namespace Feature\Domain\Gacha\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Gacha\Constants\GachaConstants;
use App\Domain\Gacha\Entities\GachaHistory;
use App\Domain\Gacha\Enums\CostType;
use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Gacha\Enums\UpperType;
use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Services\GachaService;
use App\Domain\Gacha\Services\StepupGachaService;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Domain\Resource\Mst\Models\OprGachaPrize;
use App\Domain\Resource\Mst\Models\OprGachaUpper;
use App\Domain\Resource\Mst\Models\OprStepupGacha;
use App\Domain\Resource\Mst\Models\OprStepupGachaStep;
use App\Domain\Resource\Mst\Repositories\OprGachaPrizeRepository;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GachaServiceTest extends TestCase
{
    private GachaService $service;
    private StepupGachaService $stepupGachaService;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(GachaService::class);
        $this->stepupGachaService = app(StepupGachaService::class);
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

    public function test_makeGachaRewardByGachaBoxes_prizeTypesが各GachaRewardに設定されること(): void
    {
        // Setup
        $prizeGroupId = 'prize_group_1';
        $oprGachaId = 'opr_gacha_1';

        $mstUnits = MstUnit::factory()->createMany([
            ['rarity' => RarityType::UR->value],
            ['rarity' => RarityType::SR->value],
            ['rarity' => RarityType::R->value],
        ])->map(fn ($u) => $u->toEntity());

        $mstUnits->each(function ($unit) use ($prizeGroupId) {
            OprGachaPrize::factory()->create([
                'group_id' => $prizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $unit->getId(),
                'weight' => 100,
            ]);
        });

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'prize_group_id' => $prizeGroupId,
        ]);

        $oprGacha = OprGacha::findOrFail($oprGachaId)->toEntity();
        $lotteryBox = $this->service->getGachaLotteryBox($oprGacha);
        $gachaBoxes = $lotteryBox->getRegularLotteryBox()->take(3)->values();

        $prizeTypes = [
            GachaPrizeType::REGULAR->value,
            GachaPrizeType::FIXED->value,
            GachaPrizeType::MAX_RARITY->value,
        ];

        // Exercise
        $rewards = $this->service->makeGachaRewardByGachaBoxes($gachaBoxes, $oprGachaId, $prizeTypes);

        // Verify
        $this->assertCount(3, $rewards);
        $this->assertEquals(GachaPrizeType::REGULAR->value, $rewards[0]->getPrizeType());
        $this->assertEquals(GachaPrizeType::FIXED->value, $rewards[1]->getPrizeType());
        $this->assertEquals(GachaPrizeType::MAX_RARITY->value, $rewards[2]->getPrizeType());
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

    public function testDrawStepupGacha_通常枠と確定枠が分離されて排出される()
    {
        // Setup
        $this->createUsrUser();

        $oprGachaId = 'stepup_gacha_1';
        $stepNumber = 3;
        $drawCount = 10;
        $fixedPrizeCount = 2;
        $prizeGroupId = 'normal_prize_group';
        $fixedPrizeGroupId = 'fixed_prize_group';

        // ガチャマスタ作成
        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
        ]);

        // ステップアップガチャマスタ作成
        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        // ステップ設定作成
        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => $stepNumber,
            'draw_count' => $drawCount,
            'fixed_prize_count' => $fixedPrizeCount,
            'prize_group_id' => $prizeGroupId,
            'fixed_prize_group_id' => $fixedPrizeGroupId,
            'cost_type' => CostType::DIAMOND->value,
            'cost_num' => 1500,
        ]);

        // ユニットマスタ作成
        $mstUnits = MstUnit::factory()->createMany([
            ['rarity' => RarityType::R->value],
            ['rarity' => RarityType::SR->value],
            ['rarity' => RarityType::SSR->value],
            ['rarity' => RarityType::UR->value],
        ])->map(fn ($mstUnit) => $mstUnit->toEntity());

        // 通常枠の景品設定（全レアリティ）
        $mstUnits->each(function ($mstUnit) use ($prizeGroupId) {
            OprGachaPrize::factory()->create([
                'group_id' => $prizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->getId(),
                'resource_amount' => 1,
                'weight' => 100,
            ]);
        });

        // 確定枠の景品設定（SR以上のみ）
        $mstUnits->filter(function ($mstUnit) {
            return in_array($mstUnit->getRarity(), [
                RarityType::SR->value,
                RarityType::SSR->value,
                RarityType::UR->value
            ]);
        })->each(function ($mstUnit) use ($fixedPrizeGroupId) {
            OprGachaPrize::factory()->create([
                'group_id' => $fixedPrizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->getId(),
                'resource_amount' => 1,
                'weight' => 100,
            ]);
        });

        $oprGacha = OprGacha::findOrFail($oprGachaId)->toEntity();
        $oprStepupGachaStep = OprStepupGachaStep::where('opr_gacha_id', $oprGachaId)
            ->where('step_number', $stepNumber)
            ->firstOrFail()
            ->toEntity();

        // Exercise
        // ステップアップガチャ用の抽選BOXを取得
        $gachaLotteryBox = $this->stepupGachaService->getLotteryBox($oprGacha, $oprStepupGachaStep);
        // 統一されたexecuteLottery()メソッドで抽選（fixedPrizeCountを指定）
        $result = $this->service->executeLottery(
            $oprGacha,
            $gachaLotteryBox,
            $drawCount,
            collect(), // oprGachaUppers（ステップアップガチャには天井なし）
            collect(), // usrGachaUppers
            false, // isDrawAd
            $fixedPrizeCount, // 確定枠の数
            true // ステップアップガチャでは最低連数チェックをスキップ
        );

        // Verify
        $prizes = $result->getResult();
        $this->assertCount($drawCount, $prizes);

        // toArray()でprize_typesを取得
        $resultArray = $result->toArray();
        $prizeTypes = $resultArray['prize_types'];

        // 通常枠と確定枠の数を確認
        $normalCount = count(array_filter($prizeTypes, fn($type) => $type === \App\Domain\Gacha\Enums\GachaPrizeType::REGULAR->value));
        $fixedCount = count(array_filter($prizeTypes, fn($type) => $type === \App\Domain\Gacha\Enums\GachaPrizeType::FIXED->value));

        $this->assertEquals($drawCount - $fixedPrizeCount, $normalCount);
        $this->assertEquals($fixedPrizeCount, $fixedCount);

        // 確定枠は全てSR以上
        $fixedPrizes = $prizes->filter(function ($prize, $index) use ($prizeTypes) {
            return $prizeTypes[$index] === \App\Domain\Gacha\Enums\GachaPrizeType::FIXED->value;
        });

        foreach ($fixedPrizes as $prize) {
            $unitId = $prize->getResourceId();
            $unit = $mstUnits->first(fn ($u) => $u->getId() === $unitId);
            $this->assertNotNull($unit);
            $this->assertContains($unit->getRarity(), [
                RarityType::SR->value,
                RarityType::SSR->value,
                RarityType::UR->value
            ]);
        }
    }

    public function testProgressStepupGachaStep_ステップが進行する()
    {
        // Setup
        $usrUserId = 'user_1';
        $oprGachaId = 'stepup_gacha_1';
        $currentStepNumber = 3;

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => 5,
            'max_loop_count' => 3,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUserId,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => $currentStepNumber,
            'loop_count' => 0,
        ]);

        $stepupGachaRepo = app(\App\Domain\Resource\Mst\Repositories\OprStepupGachaRepository::class);
        $stepupGacha = $stepupGachaRepo->findByOprGachaId($oprGachaId);

        // Exercise
        $this->stepupGachaService->progressStep($usrGacha, $stepupGacha);
        $usrGacha->save();

        // Verify
        $usrGacha->refresh();
        $this->assertEquals(4, $usrGacha->current_step_number);
        $this->assertEquals(0, $usrGacha->loop_count);
    }

    public function testProgressStepupGachaStep_最終ステップで周回数が増える()
    {
        // Setup
        $usrUserId = 'user_1';
        $oprGachaId = 'stepup_gacha_1';
        $maxStepNumber = 5;

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => 3,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUserId,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => $maxStepNumber,
            'loop_count' => 1,
        ]);

        $stepupGachaRepo = app(\App\Domain\Resource\Mst\Repositories\OprStepupGachaRepository::class);
        $stepupGacha = $stepupGachaRepo->findByOprGachaId($oprGachaId);

        // Exercise
        $this->stepupGachaService->progressStep($usrGacha, $stepupGacha);
        $usrGacha->save();

        // Verify
        $usrGacha->refresh();
        $this->assertEquals(1, $usrGacha->current_step_number);
        $this->assertEquals(2, $usrGacha->loop_count);
    }

    public function testProgressStepupGachaStep_最大周回数到達でエラー()
    {
        // Setup
        $usrUserId = 'user_1';
        $oprGachaId = 'stepup_gacha_1';
        $maxStepNumber = 5;
        $maxLoopCount = 3;

        OprStepupGacha::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'max_step_number' => $maxStepNumber,
            'max_loop_count' => $maxLoopCount,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUserId,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => $maxStepNumber,
            'loop_count' => $maxLoopCount,
        ]);

        $stepupGachaRepo = app(\App\Domain\Resource\Mst\Repositories\OprStepupGachaRepository::class);
        $stepupGacha = $stepupGachaRepo->findByOprGachaId($oprGachaId);

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::GACHA_STEPUP_MAX_LOOP_COUNT_EXCEEDED);

        $this->stepupGachaService->progressStep($usrGacha, $stepupGacha);
    }

    public static function params_testExecuteLottery_skipMinimumDrawCountCheckで確定枠制御(): array
    {
        return [
            'スキップあり_1連_確定枠1_確定枠が適用される' => [
                'drawCount' => 1,
                'fixedPrizeCount' => 1,
                'skipMinimumDrawCountCheck' => true,
                'expectedRegularCount' => 0,
                'expectedFixedCount' => 1,
            ],
            'スキップあり_5連_確定枠1_確定枠が適用される' => [
                'drawCount' => 5,
                'fixedPrizeCount' => 1,
                'skipMinimumDrawCountCheck' => true,
                'expectedRegularCount' => 4,
                'expectedFixedCount' => 1,
            ],
            'スキップあり_5連_確定枠3_確定枠が適用される' => [
                'drawCount' => 5,
                'fixedPrizeCount' => 3,
                'skipMinimumDrawCountCheck' => true,
                'expectedRegularCount' => 2,
                'expectedFixedCount' => 3,
            ],
            'スキップなし_5連_確定枠1_確定枠が適用されない' => [
                'drawCount' => 5,
                'fixedPrizeCount' => 1,
                'skipMinimumDrawCountCheck' => false,
                'expectedRegularCount' => 5,
                'expectedFixedCount' => 0,
            ],
            'スキップなし_1連_確定枠1_確定枠が適用されない' => [
                'drawCount' => 1,
                'fixedPrizeCount' => 1,
                'skipMinimumDrawCountCheck' => false,
                'expectedRegularCount' => 1,
                'expectedFixedCount' => 0,
            ],
            'スキップなし_10連_確定枠1_10連以上なので確定枠が適用される' => [
                'drawCount' => 10,
                'fixedPrizeCount' => 1,
                'skipMinimumDrawCountCheck' => false,
                'expectedRegularCount' => 9,
                'expectedFixedCount' => 1,
            ],
        ];
    }

    #[DataProvider('params_testExecuteLottery_skipMinimumDrawCountCheckで確定枠制御')]
    public function testExecuteLottery_skipMinimumDrawCountCheckで確定枠制御(
        int $drawCount,
        int $fixedPrizeCount,
        bool $skipMinimumDrawCountCheck,
        int $expectedRegularCount,
        int $expectedFixedCount,
    ): void {
        // Setup
        $this->createUsrUser();

        $oprGachaId = 'stepup_gacha_skip_test';
        $prizeGroupId = 'normal_prize_group_skip';
        $fixedPrizeGroupId = 'fixed_prize_group_skip';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
            'prize_group_id' => $prizeGroupId,
        ]);

        // ユニットマスタ作成
        $mstUnits = MstUnit::factory()->createMany([
            ['rarity' => RarityType::R->value],
            ['rarity' => RarityType::SR->value],
            ['rarity' => RarityType::UR->value],
        ])->map(fn ($mstUnit) => $mstUnit->toEntity());

        // 通常枠の景品設定（全レアリティ）
        $mstUnits->each(function ($mstUnit) use ($prizeGroupId) {
            OprGachaPrize::factory()->create([
                'group_id' => $prizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->getId(),
                'resource_amount' => 1,
                'weight' => 100,
            ]);
        });

        // 確定枠の景品設定（SR以上のみ）
        $mstUnits->filter(function ($mstUnit) {
            return in_array($mstUnit->getRarity(), [
                RarityType::SR->value,
                RarityType::UR->value,
            ]);
        })->each(function ($mstUnit) use ($fixedPrizeGroupId) {
            OprGachaPrize::factory()->create([
                'group_id' => $fixedPrizeGroupId,
                'resource_type' => RewardType::UNIT,
                'resource_id' => $mstUnit->getId(),
                'resource_amount' => 1,
                'weight' => 100,
            ]);
        });

        $oprGacha = OprGacha::findOrFail($oprGachaId)->toEntity();

        // 抽選BOX構築
        $regularLotteryBox = app(OprGachaPrizeRepository::class)
            ->getByGroupIdWithError($prizeGroupId);
        $fixedLotteryBox = app(OprGachaPrizeRepository::class)
            ->getByGroupIdWithError($fixedPrizeGroupId);
        $gachaLotteryBox = $this->service->convertToGachaLotteryBox($regularLotteryBox, $fixedLotteryBox);

        // Exercise
        $result = $this->service->executeLottery(
            $oprGacha,
            $gachaLotteryBox,
            $drawCount,
            collect(),
            collect(),
            false,
            $fixedPrizeCount,
            $skipMinimumDrawCountCheck
        );

        // Verify
        $prizes = $result->getResult();
        $this->assertCount($drawCount, $prizes);

        $resultArray = $result->toArray();
        $prizeTypes = $resultArray['prize_types'];

        $regularCount = count(array_filter($prizeTypes, fn($type) => $type === GachaPrizeType::REGULAR->value));
        $fixedCount = count(array_filter($prizeTypes, fn($type) => $type === GachaPrizeType::FIXED->value));

        // 通常枠の数が期待値と一致すること
        $this->assertEquals($expectedRegularCount, $regularCount);
        // 確定枠の数が期待値と一致すること
        $this->assertEquals($expectedFixedCount, $fixedCount);
    }

    public static function params_testValidateStepupGachaCost_コスト検証()
    {
        return [
            '通常ステップ_ダイヤ十分' => [
                'costType' => CostType::DIAMOND,
                'cost' => 1500,
                'freeDiamond' => 2000,
                'paidDiamond' => 0,
                'stepNumber' => 1,
                'loopCount' => 0,
                'isFirstFree' => false,
                'expectError' => false,
            ],
            '通常ステップ_ダイヤ不足' => [
                'costType' => CostType::DIAMOND,
                'cost' => 1500,
                'freeDiamond' => 1000,
                'paidDiamond' => 0,
                'stepNumber' => 1,
                'loopCount' => 0,
                'isFirstFree' => false,
                'expectError' => false,
            ],
            '初回無料_1周目' => [
                'costType' => CostType::FREE,
                'cost' => 0,
                'freeDiamond' => 0,
                'paidDiamond' => 0,
                'stepNumber' => 1,
                'loopCount' => 0,
                'isFirstFree' => true,
                'expectError' => false,
            ],
            '初回無料_2周目以降はダイヤ必要' => [
                'costType' => CostType::DIAMOND,
                'cost' => 1500,
                'freeDiamond' => 0,
                'paidDiamond' => 0,
                'stepNumber' => 1,
                'loopCount' => 1,
                'isFirstFree' => true,
                'expectError' => false,
            ],
            '有償ダイヤ_有償のみで支払い' => [
                'costType' => CostType::PAID_DIAMOND,
                'cost' => 1500,
                'freeDiamond' => 0,
                'paidDiamond' => 2000,
                'stepNumber' => 1,
                'loopCount' => 0,
                'isFirstFree' => false,
                'expectError' => false,
            ],
            '有償ダイヤ_有償不足' => [
                'costType' => CostType::PAID_DIAMOND,
                'cost' => 1500,
                'freeDiamond' => 5000,
                'paidDiamond' => 1000,
                'stepNumber' => 1,
                'loopCount' => 0,
                'isFirstFree' => false,
                'expectError' => false,
            ],
        ];
    }

    #[DataProvider('params_testValidateStepupGachaCost_コスト検証')]
    public function testValidateStepupGachaCost_コスト検証(
        CostType $costType,
        int $cost,
        int $freeDiamond,
        int $paidDiamond,
        int $stepNumber,
        int $loopCount,
        bool $isFirstFree,
        bool $expectError
    ) {
        // Setup
        $this->createUsrUser();
        $usrUserId = $this->usrUserId;
        $oprGachaId = 'stepup_gacha_1';

        OprGacha::factory()->create([
            'id' => $oprGachaId,
            'gacha_type' => GachaType::STEPUP->value,
        ]);

        OprStepupGachaStep::factory()->create([
            'opr_gacha_id' => $oprGachaId,
            'step_number' => $stepNumber,
            'cost_type' => $costType->value,
            'cost_num' => $cost,
            'is_first_free' => $isFirstFree,
        ]);

        $usrGacha = UsrGacha::factory()->create([
            'usr_user_id' => $usrUserId,
            'opr_gacha_id' => $oprGachaId,
            'current_step_number' => $stepNumber,
            'loop_count' => $loopCount,
        ]);

        if ($expectError) {
            $this->expectException(GameException::class);
        }

        $oprStepupGachaStep = OprStepupGachaStep::where('opr_gacha_id', $oprGachaId)
            ->where('step_number', $stepNumber)
            ->firstOrFail()
            ->toEntity();

        $oprGacha = OprGacha::find($oprGachaId)->toEntity();

        // Exercise
        $this->stepupGachaService->validateCost(
            $oprGacha,
            $oprStepupGachaStep,
            $costType,
            null,
            $cost,
            $loopCount
        );

        // Verify
        if (!$expectError) {
            $this->assertTrue(true);
        }
    }
}
