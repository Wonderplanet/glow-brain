<?php

namespace Feature\Domain\Unit\Services;

use App\Domain\Encyclopedia\Enums\EncyclopediaEffectType;
use App\Domain\Resource\Entities\CheatCheckUnit;
use App\Domain\Resource\Entities\UnitAudit;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstEventBonusUnit;
use App\Domain\Resource\Mst\Models\MstQuestEventBonusSchedule;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use App\Domain\Resource\Mst\Models\MstUnitGradeCoefficient;
use App\Domain\Resource\Mst\Models\MstUnitGradeUp;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\MstUnitRankCoefficient;
use App\Domain\Resource\Mst\Models\MstUnitRankUp;
use App\Domain\Resource\Mst\Models\MstUnitSpecificRankUp;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Services\UnitStatusService;
use Tests\TestCase;

class UnitStatusServiceTest extends TestCase
{
    private UnitStatusService $unitStatusService;

    public function setUp(): void
    {
        parent::setUp();
        $this->unitStatusService = $this->app->make(UnitStatusService::class);
    }

    private function createMstData(string $unitLabel): void
    {
        MstUnitLevelUp::factory()->createMany([
            [
                'unit_label' => $unitLabel,
                'level' => 1,
                'required_coin' => 1,
            ],
            [
                'unit_label' => $unitLabel,
                'level' => 2,
                'required_coin' => 2,
            ],
            [
                'unit_label' => $unitLabel,
                'level' => 3,
                'required_coin' => 3,
            ],
            [
                'unit_label' => $unitLabel,
                'level' => 4,
                'required_coin' => 4,
            ],
            [
                'unit_label' => $unitLabel,
                'level' => 5,
                'required_coin' => 5,
            ],
        ]);
        MstConfig::factory()->create([
            'key' => 'UNIT_STATUS_EXPONENT',
            'value' => 1.5,
        ]);
        MstUnitRankUp::factory()->create([
            'rank' => 1,
            'unit_label' => $unitLabel,
            'require_level' => 5,
        ]);
        MstUnitSpecificRankUp::factory()->create([
            'rank' => 1,
            'mst_unit_id' => 'unit2',
            'require_level' => 3,
        ]);
        MstUnitGradeUp::factory()->createMany([
            [
                'unit_label' => $unitLabel,
                'grade_level' => 1,
            ],
            [
                'unit_label' => $unitLabel,
                'grade_level' => 2,
            ]
        ]);
        MstUnitRankCoefficient::factory()->createMany([
            [
                'rank' => 1,
                'coefficient' => 1,
            ],
            [
                'rank' => 2,
                'coefficient' => 2,
            ],
            [
                'rank' => 3,
                'coefficient' => 3,
            ],
            [
                'rank' => 4,
                'coefficient' => 4,
            ],
            [
                'rank' => 5,
                'coefficient' => 5,
            ],
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            [
                'unit_label' => $unitLabel,
                'grade_level' => 1,
                'coefficient' => 1,
            ],
            [
                'unit_label' => $unitLabel,
                'grade_level' => 2,
                'coefficient' => 2,
            ],
            [
                'unit_label' => $unitLabel,
                'grade_level' => 3,
                'coefficient' => 3,
            ],
            [
                'unit_label' => $unitLabel,
                'grade_level' => 4,
                'coefficient' => 4,
            ],
            [
                'unit_label' => $unitLabel,
                'grade_level' => 5,
                'coefficient' => 5,
            ],
        ]);
    }

    private function createUnitData(string $mstUnitId, string $unitLabel): array
    {
        $mstUnit = MstUnit::factory()->create([
            'id' => $mstUnitId,
            'unit_label' => $unitLabel,
            'min_hp' => 1000,
            'max_hp' => 5000,
            'min_attack_power' => 20000,
            'max_attack_power' => 60000,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->createAndConvert([
            'mst_unit_id' => $mstUnitId,
            'level' => 3,
            'rank' => 1,
            'grade_level' => 2,
        ]);
        $unit = new CheatCheckUnit(
            $mstUnit->getId(),
            $usrUnit->getLevel(),
            $usrUnit->getRank(),
            $usrUnit->getGradeLevel()
        );
        return [
            'mstUnit' => $mstUnit,
            'usrUnit' => $usrUnit,
            'unit' => $unit,
        ];
    }

    public function testConvertUnitDataToUnitStatusData_正常取得()
    {
        // Setup
        $unitLabel = 'DropR';
        $mstUnitId = 'unit1';
        $this->createMstData($unitLabel);
        $unitData = $this->createUnitData($mstUnitId, $unitLabel);

        // Exercise
        $unitStatus = $this->unitStatusService->convertUnitDataToUnitStatusData(
            $unitData['mstUnit'],
            $unitData['unit'],
            5,
            5,
            1.5,
            10,
            10
        );

        // Verify
        $this->assertEquals($mstUnitId, $unitStatus->getMstUnit()->getId());
        $this->assertEquals(3155, $unitStatus->getBaseHp());
        $this->assertEquals(43556.0, $unitStatus->getBaseAtk());
        $this->assertEquals(3155, $unitStatus->getBoostedHp());
        $this->assertEquals(43556, $unitStatus->getBoostedAtk());
    }

    public function testConvertUnitDataListToUnitStatusDataList_正常取得()
    {
        // Setup
        $unitLabel = 'DropR';
        $mstUnitId1 = 'unit1';
        $mstUnitId2 = 'unit2';
        $this->createMstData($unitLabel);
        $unitDataList = collect([
            $this->createUnitData($mstUnitId1, $unitLabel)['unit'],
            $this->createUnitData($mstUnitId2, $unitLabel)['unit'],
        ]);

        // Exercise
        $unitStatuses = $this->unitStatusService->convertUnitDataListToUnitStatusDataList($unitDataList);

        // Verify
        $unitStatus = $unitStatuses->first();
        $this->assertEquals($mstUnitId1, $unitStatus->getMstUnit()->getId());
        $this->assertEquals(2512, $unitStatus->getBaseHp());
        $this->assertEquals(35424.0, $unitStatus->getBaseAtk());
        $this->assertEquals(2512, $unitStatus->getBoostedHp());
        $this->assertEquals(35424, $unitStatus->getBoostedAtk());

        $unitStatus = $unitStatuses->last();
        $this->assertEquals($mstUnitId2, $unitStatus->getMstUnit()->getId());
        $this->assertEquals(2486, $unitStatus->getBaseHp());
        $this->assertEquals(35165.0, $unitStatus->getBaseAtk());
        $this->assertEquals(2486, $unitStatus->getBoostedHp());
        $this->assertEquals(35165.0, $unitStatus->getBoostedAtk());
    }

    public function testAssignEffectBonusesToUnitStatus_正常取得()
    {
        // Setup
        $unitLabel = 'DropR';
        $mstUnitId1 = 'unit1';
        $mstUnitId2 = 'unit2';
        $eventBonusGroupId = 'event1';
        $this->createMstData($unitLabel);
        $unitData1 = $this->createUnitData($mstUnitId1, $unitLabel);
        $unitData2 = $this->createUnitData($mstUnitId2, $unitLabel);
        $unitStatuses = collect([
            new UnitAudit($unitData1['mstUnit'], $unitData1['unit'], 2001, 30007),
            new UnitAudit($unitData2['mstUnit'], $unitData2['unit'], 2011, 30073),
        ]);
        MstEventBonusUnit::factory()->create([
            'mst_unit_id' => $mstUnitId2,
            'bonus_percentage' => 150,
            'event_bonus_group_id' => $eventBonusGroupId,
        ]);
        MstUnitEncyclopediaReward::factory()->createMany([
            [
                'id' => 'reward1',
                'unit_encyclopedia_rank' => 1
            ],
            [
                'id' => 'reward2',
                'unit_encyclopedia_rank' => 3
            ],
            [
                'id' => 'reward3',
                'unit_encyclopedia_rank' => 5
            ],
        ]);
        MstUnitEncyclopediaEffect::factory()->createMany([
            [
                'id' => 'effect1',
                'mst_unit_encyclopedia_reward_id' => 'reward1',
                'effect_type' => EncyclopediaEffectType::HP->value,
                'value' => 100,
            ],
            [
                'id' => 'effect2',
                'mst_unit_encyclopedia_reward_id' => 'reward1',
                'effect_type' => EncyclopediaEffectType::ATTACK_POWER->value,
                'value' => 150,
            ],
            [
                'id' => 'effect3',
                'mst_unit_encyclopedia_reward_id' => 'reward2',
                'effect_type' => EncyclopediaEffectType::HP->value,
                'value' => 110,
            ],
            [
                'id' => 'effect4',
                'mst_unit_encyclopedia_reward_id' => 'reward2',
                'effect_type' => EncyclopediaEffectType::ATTACK_POWER->value,
                'value' => 160,
            ],
        ]);

        // Exercise
        $this->unitStatusService->assignEffectBonusesToUnitStatus(
            $unitStatuses,
            $eventBonusGroupId,
            collect(['effect1', 'effect2', 'effect3', 'effect4'])
        );

        // Verify
        // 図鑑効果が適用されていることを確認
        /** @var UnitAudit $unitStatus */
        $unitStatus = $unitStatuses->first();
        $this->assertEquals($mstUnitId1, $unitStatus->getMstUnit()->getId());
        $this->assertEquals(2001, $unitStatus->getBaseHp());
        $this->assertEquals(30007, $unitStatus->getBaseAtk());
        $this->assertEquals(6204, $unitStatus->getBoostedHp());
        $this->assertEquals(123029, $unitStatus->getBoostedAtk());

        // 図鑑効果とイベントボーナスが適用されていることを確認
        /** @var UnitAudit $unitStatus */
        $unitStatus = $unitStatuses->skip(1)->first();
        $this->assertEquals($mstUnitId2, $unitStatus->getMstUnit()->getId());
        $this->assertEquals(2011, $unitStatus->getBaseHp());
        $this->assertEquals(30073, $unitStatus->getBaseAtk());
        $this->assertEquals(15586, $unitStatus->getBoostedHp());
        $this->assertEquals(308249, $unitStatus->getBoostedAtk());
    }

    public function test_convertUnitDataListToUnitStatusDataList_hp計算()
    {
        $now = $this->fixTime();

        // Setup
        MstConfig::factory()->create([
            'key' => 'UNIT_STATUS_EXPONENT',
            'value' => 1.1,
        ]);

        MstUnit::factory()->createMany([
            ['id' => 'chara_aka_00001', 'unit_label' => 'PremiumR', 'min_hp' => 3190, 'max_hp' => 31900, 'role_type' => 'Defense', 'has_specific_rank_up' => 0],
            ['id' => 'chara_aka_00101', 'unit_label' => 'PremiumR', 'min_hp' => 690, 'max_hp' => 6900, 'role_type' => 'Technical', 'has_specific_rank_up' => 0],
            ['id' => 'chara_chi_00001', 'unit_label' => 'PremiumR', 'min_hp' => 620, 'max_hp' => 6200, 'role_type' => 'Attack', 'has_specific_rank_up' => 0],
            ['id' => 'chara_kai_00001', 'unit_label' => 'PremiumR', 'min_hp' => 500, 'max_hp' => 5000, 'role_type' => 'Attack', 'has_specific_rank_up' => 0],
            ['id' => 'chara_sum_00001', 'unit_label' => 'PremiumR', 'min_hp' => 620, 'max_hp' => 6200, 'role_type' => 'Technical', 'has_specific_rank_up' => 0],
            ['id' => 'chara_sur_00001', 'unit_label' => 'PremiumR', 'min_hp' => 650, 'max_hp' => 6500, 'role_type' => 'Support', 'has_specific_rank_up' => 0],
            ['id' => 'chara_bat_00001', 'unit_label' => 'PremiumSR', 'min_hp' => 330, 'max_hp' => 3300, 'role_type' => 'Attack', 'has_specific_rank_up' => 0],
            ['id' => 'chara_gom_00101', 'unit_label' => 'PremiumSR', 'min_hp' => 690, 'max_hp' => 6900, 'role_type' => 'Technical', 'has_specific_rank_up' => 0],
            ['id' => 'chara_sur_00301', 'unit_label' => 'PremiumSR', 'min_hp' => 2740, 'max_hp' => 27400, 'role_type' => 'Defense', 'has_specific_rank_up' => 0],
            ['id' => 'chara_kai_00002', 'unit_label' => 'PremiumUR', 'min_hp' => 2800, 'max_hp' => 28000, 'role_type' => 'Attack', 'has_specific_rank_up' => 0],
        ]);

        // Create level up data for all unit labels and levels
        MstUnitLevelUp::factory()->createMany([
            // DropR level data
            ['id' => '1', 'unit_label' => 'DropR', 'level' => 1],
            ['id' => '2', 'unit_label' => 'DropR', 'level' => 2],
            ['id' => '3', 'unit_label' => 'DropR', 'level' => 3],
            ['id' => '4', 'unit_label' => 'DropR', 'level' => 4],
            ['id' => '5', 'unit_label' => 'DropR', 'level' => 5],
            ['id' => '6', 'unit_label' => 'DropR', 'level' => 6],
            ['id' => '7', 'unit_label' => 'DropR', 'level' => 7],
            ['id' => '8', 'unit_label' => 'DropR', 'level' => 8],
            ['id' => '9', 'unit_label' => 'DropR', 'level' => 9],
            ['id' => '10', 'unit_label' => 'DropR', 'level' => 10],
            ['id' => '11', 'unit_label' => 'DropR', 'level' => 11],
            ['id' => '12', 'unit_label' => 'DropR', 'level' => 12],
            ['id' => '13', 'unit_label' => 'DropR', 'level' => 13],
            ['id' => '14', 'unit_label' => 'DropR', 'level' => 14],
            ['id' => '15', 'unit_label' => 'DropR', 'level' => 15],
            ['id' => '16', 'unit_label' => 'DropR', 'level' => 16],
            ['id' => '17', 'unit_label' => 'DropR', 'level' => 17],
            ['id' => '18', 'unit_label' => 'DropR', 'level' => 18],
            ['id' => '19', 'unit_label' => 'DropR', 'level' => 19],
            ['id' => '20', 'unit_label' => 'DropR', 'level' => 20],

            // PremiumR level data
            ['id' => '451', 'unit_label' => 'PremiumR', 'level' => 1],
            ['id' => '452', 'unit_label' => 'PremiumR', 'level' => 2],
            ['id' => '453', 'unit_label' => 'PremiumR', 'level' => 3],
            ['id' => '454', 'unit_label' => 'PremiumR', 'level' => 4],
            ['id' => '455', 'unit_label' => 'PremiumR', 'level' => 5],
            ['id' => '456', 'unit_label' => 'PremiumR', 'level' => 6],
            ['id' => '457', 'unit_label' => 'PremiumR', 'level' => 7],
            ['id' => '458', 'unit_label' => 'PremiumR', 'level' => 8],
            ['id' => '459', 'unit_label' => 'PremiumR', 'level' => 9],
            ['id' => '460', 'unit_label' => 'PremiumR', 'level' => 10],
            ['id' => '470', 'unit_label' => 'PremiumR', 'level' => 20],
            ['id' => '600', 'unit_label' => 'PremiumR', 'level' => 150],

            // PremiumSR level data
            ['id' => '601', 'unit_label' => 'PremiumSR', 'level' => 1],
            ['id' => '602', 'unit_label' => 'PremiumSR', 'level' => 2],
            ['id' => '603', 'unit_label' => 'PremiumSR', 'level' => 3],
            ['id' => '604', 'unit_label' => 'PremiumSR', 'level' => 4],
            ['id' => '605', 'unit_label' => 'PremiumSR', 'level' => 5],
            ['id' => '620', 'unit_label' => 'PremiumSR', 'level' => 20],

            // PremiumUR level data
            ['id' => '901', 'unit_label' => 'PremiumUR', 'level' => 1],
            ['id' => '902', 'unit_label' => 'PremiumUR', 'level' => 2],
            ['id' => '903', 'unit_label' => 'PremiumUR', 'level' => 3],
            ['id' => '904', 'unit_label' => 'PremiumUR', 'level' => 4],
            ['id' => '905', 'unit_label' => 'PremiumUR', 'level' => 5],
            ['id' => '920', 'unit_label' => 'PremiumUR', 'level' => 20],
            ['id' => '1000', 'unit_label' => 'PremiumUR', 'level' => 100],
            ['id' => '1050', 'unit_label' => 'PremiumUR', 'level' => 150]
        ]);


        // ランク係数
        MstUnitRankCoefficient::factory()->createMany([
            ['rank' => 1, 'coefficient' => 6, 'special_unit_coefficient' => 43],
            ['rank' => 2, 'coefficient' => 9, 'special_unit_coefficient' => 66],
            ['rank' => 3, 'coefficient' => 11, 'special_unit_coefficient' => 100],
            ['rank' => 4, 'coefficient' => 12, 'special_unit_coefficient' => 149],
            ['rank' => 5, 'coefficient' => 15, 'special_unit_coefficient' => 216],
            ['rank' => 6, 'coefficient' => 16, 'special_unit_coefficient' => 300],
        ]);

        // グレード係数を作成
        MstUnitGradeCoefficient::factory()->createMany([
            ['id' => 1, 'unit_label' => 'DropR', 'grade_level' => 1, 'coefficient' => 0],
            ['id' => 2, 'unit_label' => 'DropR', 'grade_level' => 2, 'coefficient' => 3],
            ['id' => 3, 'unit_label' => 'DropR', 'grade_level' => 3, 'coefficient' => 5],
            ['id' => 4, 'unit_label' => 'DropR', 'grade_level' => 4, 'coefficient' => 8],
            ['id' => 5, 'unit_label' => 'DropR', 'grade_level' => 5, 'coefficient' => 10],
            ['id' => 11, 'unit_label' => 'DropSR', 'grade_level' => 1, 'coefficient' => 0],
            ['id' => 12, 'unit_label' => 'DropSR', 'grade_level' => 2, 'coefficient' => 5],
            ['id' => 13, 'unit_label' => 'DropSR', 'grade_level' => 3, 'coefficient' => 7],
            ['id' => 14, 'unit_label' => 'DropSR', 'grade_level' => 4, 'coefficient' => 10],
            ['id' => 15, 'unit_label' => 'DropSR', 'grade_level' => 5, 'coefficient' => 12],
            ['id' => 21, 'unit_label' => 'DropSSR', 'grade_level' => 1, 'coefficient' => 0],
            ['id' => 22, 'unit_label' => 'DropSSR', 'grade_level' => 2, 'coefficient' => 7],
            ['id' => 23, 'unit_label' => 'DropSSR', 'grade_level' => 3, 'coefficient' => 9],
            ['id' => 24, 'unit_label' => 'DropSSR', 'grade_level' => 4, 'coefficient' => 12],
            ['id' => 25, 'unit_label' => 'DropSSR', 'grade_level' => 5, 'coefficient' => 14],
            ['id' => 31, 'unit_label' => 'PremiumR', 'grade_level' => 1, 'coefficient' => 0],
            ['id' => 32, 'unit_label' => 'PremiumR', 'grade_level' => 2, 'coefficient' => 8],
            ['id' => 33, 'unit_label' => 'PremiumR', 'grade_level' => 3, 'coefficient' => 11],
            ['id' => 34, 'unit_label' => 'PremiumR', 'grade_level' => 4, 'coefficient' => 14],
            ['id' => 35, 'unit_label' => 'PremiumR', 'grade_level' => 5, 'coefficient' => 16],
            ['id' => 41, 'unit_label' => 'PremiumSR', 'grade_level' => 1, 'coefficient' => 0],
            ['id' => 42, 'unit_label' => 'PremiumSR', 'grade_level' => 2, 'coefficient' => 9],
            ['id' => 43, 'unit_label' => 'PremiumSR', 'grade_level' => 3, 'coefficient' => 14],
            ['id' => 44, 'unit_label' => 'PremiumSR', 'grade_level' => 4, 'coefficient' => 17],
            ['id' => 45, 'unit_label' => 'PremiumSR', 'grade_level' => 5, 'coefficient' => 19],
            ['id' => 51, 'unit_label' => 'PremiumSSR', 'grade_level' => 1, 'coefficient' => 0],
            ['id' => 52, 'unit_label' => 'PremiumSSR', 'grade_level' => 2, 'coefficient' => 12],
            ['id' => 53, 'unit_label' => 'PremiumSSR', 'grade_level' => 3, 'coefficient' => 19],
            ['id' => 54, 'unit_label' => 'PremiumSSR', 'grade_level' => 4, 'coefficient' => 23],
            ['id' => 55, 'unit_label' => 'PremiumSSR', 'grade_level' => 5, 'coefficient' => 25],
            ['id' => 61, 'unit_label' => 'PremiumUR', 'grade_level' => 1, 'coefficient' => 0],
            ['id' => 62, 'unit_label' => 'PremiumUR', 'grade_level' => 2, 'coefficient' => 16],
            ['id' => 63, 'unit_label' => 'PremiumUR', 'grade_level' => 3, 'coefficient' => 26],
            ['id' => 64, 'unit_label' => 'PremiumUR', 'grade_level' => 4, 'coefficient' => 32],
            ['id' => 65, 'unit_label' => 'PremiumUR', 'grade_level' => 5, 'coefficient' => 35],
            ['id' => 71, 'unit_label' => 'FestivalUR', 'grade_level' => 1, 'coefficient' => 0],
            ['id' => 72, 'unit_label' => 'FestivalUR', 'grade_level' => 2, 'coefficient' => 18],
            ['id' => 73, 'unit_label' => 'FestivalUR', 'grade_level' => 3, 'coefficient' => 29],
            ['id' => 74, 'unit_label' => 'FestivalUR', 'grade_level' => 4, 'coefficient' => 34],
            ['id' => 75, 'unit_label' => 'FestivalUR', 'grade_level' => 5, 'coefficient' => 38],
        ]);

        // イベントボーナス
        MstQuestEventBonusSchedule::factory()->create([
            'event_bonus_group_id' => 'raid_kai_00001',
            'start_at' => $now->subDay(),
            'end_at' => $now->addDay(),
        ]);
        MstEventBonusUnit::factory()->createMany([
            ['mst_unit_id' => 'chara_kai_00002', 'event_bonus_group_id' => 'raid_kai_00001', 'bonus_percentage' => 30],
            ['mst_unit_id' => 'chara_kai_00001', 'event_bonus_group_id' => 'raid_kai_00001', 'bonus_percentage' => 30],
        ]);

        // キャラ図鑑ランク効果
        MstUnitEncyclopediaEffect::factory()->createMany([
            ['id' => 'unit_encyclopedia_effect_15', 'mst_unit_encyclopedia_reward_id' => 'unit_encyclopedia_reward_rank_15', 'effect_type' => EncyclopediaEffectType::HP->value, 'value' => 0.01],
            ['id' => 'unit_encyclopedia_effect_5', 'mst_unit_encyclopedia_reward_id' => 'unit_encyclopedia_reward_rank_5', 'effect_type' => EncyclopediaEffectType::HP->value, 'value' => 0.01],
        ]);

        // CheatCheckUnitを作成
        $cheatCheckUnits = collect([
            new CheatCheckUnit('chara_kai_00002', 20, 0, 5),  // Level 20, Rank 0, Grade 5
            new CheatCheckUnit('chara_kai_00001', 2, 0, 2),   // Level 2, Rank 0, Grade 2
            new CheatCheckUnit('chara_bat_00001', 1, 0, 2),   // Level 1, Rank 0, Grade 2
            new CheatCheckUnit('chara_gom_00101', 1, 0, 1),   // Level 1, Rank 0, Grade 1
            new CheatCheckUnit('chara_sur_00301', 1, 0, 1),   // Level 1, Rank 0, Grade 1
            new CheatCheckUnit('chara_chi_00001', 1, 0, 1),   // Level 1, Rank 0, Grade 1
            new CheatCheckUnit('chara_sum_00001', 1, 0, 1),   // Level 1, Rank 0, Grade 1
            new CheatCheckUnit('chara_sur_00001', 1, 0, 1),   // Level 1, Rank 0, Grade 1
            new CheatCheckUnit('chara_aka_00001', 1, 0, 1),   // Level 1, Rank 0, Grade 1
            new CheatCheckUnit('chara_aka_00101', 1, 0, 1),   // Level 1, Rank 0, Grade 1
        ]);

        // Exercise
        $unitAudits = $this->unitStatusService->convertUnitDataListToUnitStatusDataList($cheatCheckUnits);
        $this->unitStatusService->assignEffectBonusesToUnitStatus(
            $unitAudits,
            'raid_kai_00001',
            collect(['unit_encyclopedia_effect_5', 'unit_encyclopedia_effect_15'])
        );

        // Verify
        $expectedResults = [
            // hp = 基礎HP + ランク補正 + グレード補正
            ['mst_unit_id' => 'chara_kai_00002', 'hp' => 9505],  // Level 20, grade grade bonus
            ['mst_unit_id' => 'chara_kai_00001', 'hp' => 727],    // Level 2, grade bonus
            ['mst_unit_id' => 'chara_bat_00001', 'hp' => 360],   // Level 1, no grade bonus
            ['mst_unit_id' => 'chara_gom_00101', 'hp' => 691],    // Level 1, no grade bonus
            ['mst_unit_id' => 'chara_sur_00301', 'hp' => 2741],   // Level 1, no grade bonus
            ['mst_unit_id' => 'chara_chi_00001', 'hp' => 621],    // Level 1, no grade bonus
            ['mst_unit_id' => 'chara_sum_00001', 'hp' => 621],   // Level 1, no grade bonus
            ['mst_unit_id' => 'chara_sur_00001', 'hp' => 651],    // Level 1, no grade bonus
            ['mst_unit_id' => 'chara_aka_00001', 'hp' => 3191],  // Level 1, no grade bonus
            ['mst_unit_id' => 'chara_aka_00101', 'hp' => 691],    // Level 1, no grade bonus
        ];

        $this->assertCount(10, $unitAudits);

        foreach ($expectedResults as $index => $expected) {
            $unitStatus = $unitAudits->get($index);
            $this->assertEquals($expected['mst_unit_id'], $unitStatus->getMstUnit()->getId(), "Unit ID mismatch at index {$index}");
            $this->assertEquals($expected['hp'], $unitStatus->getBoostedHp(), "HP mismatch for {$expected['mst_unit_id']}");
        }
    }
}
