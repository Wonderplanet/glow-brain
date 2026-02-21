<?php

namespace Tests\Feature\Domain\Unit\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use App\Domain\Resource\Mst\Models\MstUnitSpecificRankUp;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Services\UnitLevelUpService;
use App\Domain\User\Models\UsrUserParameter;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UnitLevelUpServiceTest extends TestCase
{
    private UnitLevelUpService $unitLevelUpService;

    public function setUp(): void
    {
        parent::setUp();
        $this->unitLevelUpService = $this->app->make(UnitLevelUpService::class);
    }

    public function testLevelUp_レベルアップを実行()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'coin' => 150,
        ]);
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'level'       => 1,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);
        MstUnitLevelUp::factory()->createMany([
            [
                'unit_label' => $mstUnit->getUnitLabel(),
                'level' => $usrUnit->getLevel() + 1,
                'required_coin' => 50,
            ],
            [
                'unit_label' => $mstUnit->getUnitLabel(),
                'level' => $usrUnit->getLevel() + 2,
                'required_coin' => 100,
            ],
        ]);

        // Exercise
        $targetLevel = $usrUnit->getLevel() + 2;
        $this->unitLevelUpService->levelUp($usrUser->getId(), $usrUnit->getId(), $targetLevel, CarbonImmutable::now());
        $this->saveAll();

        // レベルが更新されていること
        $usrUnit = UsrUnit::query()->where('id', $usrUnit->getId())->first();
        $this->assertEquals($targetLevel, $usrUnit->getLevel());

        // コインが減っていること
        $usrUserParameter->refresh();
        $this->assertEquals(0, $usrUserParameter->getCoin());
    }

    public function testLevelUp_レベルアップのマスターデータがない場合エラーになる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $mstUnit = MstUnit::factory()->create()->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_unit_id' => $mstUnit->getId(),
            'level'       => 1,
        ]);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::MST_NOT_FOUND);

        // Exercise
        $this->unitLevelUpService->levelUp($usrUser->getId(), $usrUnit->getId(), $usrUnit->getLevel() + 1, CarbonImmutable::now());
    }

    public static function params_getMaxLevel_最大レベルを取得()
    {
        return [
            '最大ランクではない場合は次のランクのrequire_levelを取得' => [
                'currentRank' => 1,
                'hasSpecificRankUp' => 0,
                'expected' => 4
            ],
            '最大ランクの場合は最大のレベルアップ情報のlevelを取得' => [
                'currentRank' => 2,
                'hasSpecificRankUp' => 0,
                'expected' => 5
            ],
            'specific 最大ランクではない場合は次のランクのrequire_levelを取得' => [
                'currentRank' => 1,
                'hasSpecificRankUp' => 1,
                'expected' => 3
            ],
            'specific 最大ランクの場合は最大のレベルアップ情報のlevelを取得' => [
                'currentRank' => 2,
                'hasSpecificRankUp' => 1,
                'expected' => 5
            ],
        ];
    }


    #[DataProvider('params_getMaxLevel_最大レベルを取得')]
    public function testGetMaxLevel_最大レベルを取得(int $currentRank, int $hasSpecificRankUp, int $expected)
    {
        // Setup
        $unitLabel = 'DropR';
        $mstUnit = MstUnit::factory()->create([
            'unit_label' => $unitLabel,
            'has_specific_rank_up' => $hasSpecificRankUp,
        ])->toEntity();
        MstUnitLevelUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'level' => 1],
            ['unit_label' => $unitLabel, 'level' => 2],
            ['unit_label' => $unitLabel, 'level' => 3],
            ['unit_label' => $unitLabel, 'level' => 4],
            ['unit_label' => $unitLabel, 'level' => 5]
        ]);
        MstUnitRankUp::factory()->createMany([
            ['rank' => 1, 'unit_label' => $unitLabel, 'require_level' => 2],
            ['rank' => 2, 'unit_label' => $unitLabel, 'require_level' => 4]
        ]);
        MstUnitSpecificRankUp::factory()->createMany([
            ['mst_unit_id' => $mstUnit->getId(), 'rank' => 1, 'require_level' => 2],
            ['mst_unit_id' => $mstUnit->getId(), 'rank' => 2, 'require_level' => 3]
        ]);

        // Exercise
        $actual = $this->unitLevelUpService->getMaxLevel($currentRank, $mstUnit);

        // Verify
        $this->assertEquals($expected, $actual);
    }

    public static function params_validateLevel_レベルの検証()
    {
        return [
            '正常' => [
                'targetLevel' => 2,
                'currentLevel' => 1,
                'maxLevel' => 2,
                'errorCode' => null
            ],
            '対象レベル値不正' => [
                'targetLevel' => 0,
                'currentLevel' => 1,
                'maxLevel' => 2,
                'errorCode' => ErrorCode::UNIT_LEVE_UP_INVALID_LEVEL
            ],
            '対象レベルが現在のレベル以下' => [
                'targetLevel' => 1,
                'currentLevel' => 1,
                'maxLevel' => 2,
                'errorCode' => ErrorCode::UNIT_LEVE_UP_INVALID_LEVEL
            ],
            '対象レベルが上限レベルを超える' => [
                'targetLevel' => 3,
                'currentLevel' => 1,
                'maxLevel' => 2,
                'errorCode' => ErrorCode::UNIT_LEVEL_UP_EXCEED_LIMIT_LEVEL
            ],
        ];
    }

    #[DataProvider('params_validateLevel_レベルの検証')]
    public function testValidateLevel_レベルの検証(
        int $targetLevel,
        int $currentLevel,
        int $maxLevel,
        ?int $errorCode
    ) {
        if (!is_null($errorCode)) {
            // エラーが発生する
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }
        $this->execPrivateMethod(
            $this->unitLevelUpService,
            'validateLevel',
            [$targetLevel, $currentLevel, $maxLevel]
        );
        // エラーが起きないテストはassertがないのでダミーでassertを入れる
        $this->assertTrue(true);
    }
}
