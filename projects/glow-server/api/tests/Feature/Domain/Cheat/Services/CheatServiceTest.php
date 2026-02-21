<?php

namespace Feature\Domain\Cheat\Services;

use App\Domain\Cheat\Enums\CheatContentType;
use App\Domain\Cheat\Enums\CheatType;
use App\Domain\Cheat\Models\LogSuspectedUser;
use App\Domain\Cheat\Models\UsrCheatSession;
use App\Domain\Cheat\Services\CheatService;
use App\Domain\Encyclopedia\Enums\EncyclopediaEffectType;
use App\Domain\Party\Models\Eloquent\UsrArtworkParty;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Entities\ArtworkPartyStatus;
use App\Domain\Resource\Entities\PartyStatus;
use App\Domain\Resource\Mst\Entities\MstAttackEntity;
use App\Domain\Resource\Mst\Entities\MstUnitEntity;
use App\Domain\Resource\Mst\Models\MstAttack;
use App\Domain\Resource\Mst\Models\MstCheatSetting;
use App\Domain\Resource\Mst\Models\MstEventBonusUnit;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use App\Domain\Resource\Mst\Models\MstUnitGradeCoefficient;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Unit\Enums\AttackKind;
use App\Domain\Unit\Enums\RoleType;
use App\Domain\Unit\Enums\UnitColorType;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Models\UsrUnitSummary;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CheatServiceTest extends TestCase
{
    private CheatService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CheatService::class);
    }

    public static function params_testCheckBattleTime_チート検出()
    {
        return [
            '降臨バトル_チートなし_大きな値' => [CheatContentType::ADVENT_BATTLE, 100, 99999999, false],
            '降臨バトル_チートなし_境界値の直前' => [CheatContentType::ADVENT_BATTLE, 100, 101, false],
            '降臨バトル_チートあり_境界値そのもの' => [CheatContentType::ADVENT_BATTLE, 100, 100, true],
            '降臨バトル_チートあり_境界値の直後' => [CheatContentType::ADVENT_BATTLE, 100, 99, true],
            '降臨バトル_チートあり_ゼロ' => [CheatContentType::ADVENT_BATTLE, 100, 0, true],
            '降臨バトル_チートあり_負の値' => [CheatContentType::ADVENT_BATTLE, 100, -1, true],
            'ランクマッチ_チートなし_大きな値' => [CheatContentType::PVP, 100, 100000000, false],
            'ランクマッチ_チートなし_境界値の直前' => [CheatContentType::PVP, 100, 101, false],
            'ランクマッチ_チートあり_境界値そのもの' => [CheatContentType::PVP, 100, 100, true],
            'ランクマッチ_チートあり_境界値の直後' => [CheatContentType::PVP, 100, 99, true],
            'ランクマッチ_チートあり_ゼロ' => [CheatContentType::PVP, 100, 0, true],
            'ランクマッチ_チートあり_負の値' => [CheatContentType::PVP, 100, -1, true],
        ];
    }

    #[DataProvider('params_testCheckBattleTime_チート検出')]
    public function testCheckBattleTime_チート検出(
        CheatContentType $cheatContentType,
        int $cheatValue,
        int $battleTime,
        bool $expected
    ) {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::BATTLE_TIME->value,
            'cheat_value' => $cheatValue,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        // Exercise
        $this->service->checkBattleTime(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $battleTime,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        if ($expected)
        {
            $this->assertNotNull($logSuspectedUser);
            $this->assertEquals($cheatContentType->value, $logSuspectedUser->content_type);
            $this->assertEquals(CheatType::BATTLE_TIME->value, $logSuspectedUser->cheat_type);
            $this->assertEquals($targetId, $logSuspectedUser->target_id);
            $this->assertNotEmpty($logSuspectedUser->detail);
        }
        else
        {
            $this->assertNull($logSuspectedUser);
        }
    }


    public static function params_testCheckMaxDamage_チート検出()
    {
        return [
            '降臨バトル_チートあり_大きな値' => [CheatContentType::ADVENT_BATTLE, 10000000, 99999999, true],
            '降臨バトル_チートあり_境界値の直後' => [CheatContentType::ADVENT_BATTLE, 10000000, 10000001, true],
            '降臨バトル_チートあり_境界値そのもの' => [CheatContentType::ADVENT_BATTLE, 10000000, 10000000, true],
            '降臨バトル_チートなし_境界値の直前' => [CheatContentType::ADVENT_BATTLE, 10000000, 9999999, false],
            '降臨バトル_チートなし_ゼロ' => [CheatContentType::ADVENT_BATTLE, 10000000, 0, false],
            '降臨バトル_チートなし_負の値' => [CheatContentType::ADVENT_BATTLE, 10000000, -1, false],
            'ランクマッチ_チートあり_大きな値' => [CheatContentType::PVP, 10000000, 99999999, true],
            'ランクマッチ_チートあり_境界値の直後' => [CheatContentType::PVP, 10000000, 10000001, true],
            'ランクマッチ_チートあり_境界値そのもの' => [CheatContentType::PVP, 10000000, 10000000, true],
            'ランクマッチ_チートなし_境界値の直前' => [CheatContentType::PVP, 10000000, 9999999, false],
            'ランクマッチ_チートなし_ゼロ' => [CheatContentType::PVP, 10000000, 0, false],
            'ランクマッチ_チートなし_負の値' => [CheatContentType::PVP, 10000000, -1, false],
        ];
    }

    #[DataProvider('params_testCheckMaxDamage_チート検出')]
    public function testCheckMaxDamage_チート検出(
        CheatContentType $cheatContentType,
        int $cheatValue,
        int $damage,
        bool $expected
    ) {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::MAX_DAMAGE->value,
            'cheat_value' => $cheatValue,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        // Exercise
        $this->service->checkMaxDamage(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $damage,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        if ($expected)
        {
            $this->assertNotNull($logSuspectedUser);
            $this->assertEquals($cheatContentType->value, $logSuspectedUser->content_type);
            $this->assertEquals(CheatType::MAX_DAMAGE->value, $logSuspectedUser->cheat_type);
            $this->assertEquals($targetId, $logSuspectedUser->target_id);
            $this->assertNotEmpty($logSuspectedUser->detail);
        }
        else
        {
            $this->assertNull($logSuspectedUser);
        }
    }

    public static function params_testCheckBattleStatusMismatch_チート検出なし()
    {
        return [
            '降臨バトル' => [CheatContentType::ADVENT_BATTLE],
            'ランクマッチ' => [CheatContentType::PVP],
        ];
    }

    #[DataProvider('params_testCheckBattleStatusMismatch_チート検出なし')]
    public function testCheckBattleStatusMismatch_チート検出なし(CheatContentType $cheatContentType)
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
            ['id' => 'unit3'],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
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
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1003', 'mst_unit_id' => 'unit3', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
            ['usr_user_id' => $usrUserId, 'party_no' => 3, 'usr_unit_id_1' => 'usrUnit3', 'usr_unit_id_2' => 'usrUnit1', 'usr_unit_id_3' => 'usrUnit2'],
        ]);
        $usrUnits = UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
            ['id' => 'usrUnit3', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit3'],
        ]);

        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            /** @var UsrUnit $usrUnit */
            /** @var MstUnitEntity $mstUnit */
            /** @var MstAttackEntity $mstAttack */
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => $cheatContentType->value,
            'target_id' => $targetId,
            'party_status' => json_encode(
                $partyStatuses->map(
                    /** @var PartyStatus $partyStatus */
                    fn($partyStatus) => $partyStatus->formatToLog()
                )
            ),
        ]);

        // Exercise
        $this->service->checkBattleStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            collect(), // artworkPartyStatus
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNull($logSuspectedUser);
    }

    public static function params_testCheckBattleStatusMismatch_チート検出あり()
    {
        return [
            '降臨バトル' => [CheatContentType::ADVENT_BATTLE],
            'ランクマッチ' => [CheatContentType::PVP],
        ];
    }

    #[DataProvider('params_testCheckBattleStatusMismatch_チート検出あり')]
    public function testCheckBattleStatusMismatch_チート検出あり(CheatContentType $cheatContentType)
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
            ['id' => 'unit3'],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
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
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1003', 'mst_unit_id' => 'unit3', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
            ['usr_user_id' => $usrUserId, 'party_no' => 3, 'usr_unit_id_1' => 'usrUnit3', 'usr_unit_id_2' => 'usrUnit1', 'usr_unit_id_3' => 'usrUnit2'],
        ]);
        $usrUnits = UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
            ['id' => 'usrUnit3', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit3'],
        ]);

        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            /** @var UsrUnit $usrUnit */
            /** @var MstUnitEntity $mstUnit */
            /** @var MstAttackEntity $mstAttack */
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // Exercise
        $this->service->checkBattleStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            collect(), // artworkPartyStatus
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        /** @var LogSuspectedUser $logSuspectedUser */
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($cheatContentType->value, $logSuspectedUser->content_type);
        $this->assertEquals(CheatType::BATTLE_STATUS_MISMATCH->value, $logSuspectedUser->cheat_type);
        $this->assertEquals($targetId, $logSuspectedUser->target_id);
        $this->assertNotEmpty($logSuspectedUser->detail);
    }

    public function test_checkBattleStatusMismatch_新形式_チート検出なし()
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        $cheatContentType = CheatContentType::PVP;
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
        MstUnitLevelUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'level' => 1, 'required_coin' => 1],
            ['unit_label' => $unitLabel, 'level' => 2, 'required_coin' => 2],
            ['unit_label' => $unitLabel, 'level' => 3, 'required_coin' => 3],
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2',
        ]);
        $usrUnits = UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
        ]);

        // 原画パーティ編成を作成
        UsrArtworkParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id_1' => 'artwork_1',
            'mst_artwork_id_2' => 'artwork_2',
        ]);

        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // 原画パーティステータスを作成
        $artworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_1', 1),
            new ArtworkPartyStatus('artwork_2', 1),
        ]);

        // 新形式でセッションデータを作成
        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => $cheatContentType->value,
            'target_id' => $targetId,
            'party_status' => json_encode([
                'partyStatuses' => $partyStatuses->map(
                    fn($partyStatus) => $partyStatus->formatToLog()
                )->toArray(),
                'artworkPartyStatuses' => $artworkPartyStatus->map(
                    fn($artworkStatus) => $artworkStatus->formatToLog()
                )->toArray(),
            ]),
        ]);

        // Exercise
        $this->service->checkBattleStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            $artworkPartyStatus,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNull($logSuspectedUser);
    }

    public function test_checkBattleStatusMismatch_原画パーティ不一致_チート検出あり()
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        $cheatContentType = CheatContentType::PVP;
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
        MstUnitLevelUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'level' => 1, 'required_coin' => 1],
            ['unit_label' => $unitLabel, 'level' => 2, 'required_coin' => 2],
            ['unit_label' => $unitLabel, 'level' => 3, 'required_coin' => 3],
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2',
        ]);
        $usrUnits = UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
        ]);

        // 現在の原画パーティ編成（バトル後に変更された想定）
        UsrArtworkParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id_1' => 'artwork_changed',
            'mst_artwork_id_2' => 'artwork_2',
        ]);

        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // バトル終了時の原画パーティステータス（変更後）
        $artworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_changed', 1),
            new ArtworkPartyStatus('artwork_2', 1),
        ]);

        // セッションにはバトル開始時の原画パーティを保存（artwork_1）
        $beforeArtworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_1', 1),
            new ArtworkPartyStatus('artwork_2', 1),
        ]);
        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => $cheatContentType->value,
            'target_id' => $targetId,
            'party_status' => json_encode([
                'partyStatuses' => $partyStatuses->map(
                    fn($partyStatus) => $partyStatus->formatToLog()
                )->toArray(),
                'artworkPartyStatuses' => $beforeArtworkPartyStatus->map(
                    fn($artworkStatus) => $artworkStatus->formatToLog()
                )->toArray(),
            ]),
        ]);

        // Exercise
        $this->service->checkBattleStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            $artworkPartyStatus, // 変更後の原画パーティ
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify — 原画パーティが不一致のためチート検出
        /** @var LogSuspectedUser $logSuspectedUser */
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($cheatContentType->value, $logSuspectedUser->content_type);
        $this->assertEquals(CheatType::BATTLE_STATUS_MISMATCH->value, $logSuspectedUser->cheat_type);
        $this->assertEquals($targetId, $logSuspectedUser->target_id);
        $this->assertNotEmpty($logSuspectedUser->detail);
    }

    public function testCheckMasterDataStatusMismatch_誤検出修正確認()
    {
        // 誤検知内容
        // 1. move_speedが小数点以下.00の有無で差異があると検知される
        // 2. ユニットのアビリティはランクアップで開放され、クライアントからのパラメータは開放されている場合のみ送られてくるがサーバーは全開放前提で判定していた
        // 3. attackDelayとnextAttackIntervalがクライアントはattack_kind=Normalのものを送ってくるがサーバーはattack_kind=Specialかつgradeから取得したデータで判定していた

        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        $contentType = CheatContentType::ADVENT_BATTLE;
        MstCheatSetting::factory()->create([
            'content_type' => $contentType->value,
            'cheat_type' => CheatType::MASTER_DATA_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnit = MstUnit::factory()->create([
            'id' => 'unit1',
            'mst_unit_ability_id1' => 'ability1',
            'ability_unlock_rank1' => 1,
            'mst_unit_ability_id2' => 'ability2',
            'ability_unlock_rank2' => 2,
            'mst_unit_ability_id3' => 'ability3',
            'ability_unlock_rank3' => 3,
            'move_speed' => '50.00',
        ])->toEntity();
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
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        // チート検証ではNormalが採用されるがユニットの実グレード(2)のデータも用意しておく
        $targetSpecialMstAttackId = '1003';
        $mstAttack = MstAttack::factory()->createMany([
            [
                'id' => '1001',
                'mst_unit_id' => $mstUnit->getId(),
                'unit_grade' => 0,
                'attack_kind' => AttackKind::NORMAL->value
            ],
            [
                'id' => '1002',
                'mst_unit_id' => $mstUnit->getId(),
                'unit_grade' => 1,
                'attack_kind' => AttackKind::SPECIAL->value
            ],
            [
                'id' => $targetSpecialMstAttackId,
                'mst_unit_id' => $mstUnit->getId(),
                'unit_grade' => 2,
                'attack_kind' => AttackKind::SPECIAL->value
            ],
        ])->filter(fn($mstAttack) => $mstAttack->attack_kind === AttackKind::NORMAL->value)->first()->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'id' => 'usrUnit1',
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => $mstUnit->getId(),
            'level' => 1,
            // ability3が解放されていない状態
            'rank' => 2,
            // mst_attacks.attack_kind=Normalのunit_gradeとは違うgrade_levelを設定
            'grade_level' => 2,
        ]);
        UsrParty::factory()->create(
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => $usrUnit->getId()]
        );

        $partyStatuses = collect();
        $partyStatuses->push(new PartyStatus(
            $usrUnit->getId(),
            $usrUnit->getMstUnitId(),
            $mstUnit->getColor(),
            $mstUnit->getRoleType(),
            $mstUnit->getMinHp(),
            $mstUnit->getMinAttackPower(),
            // 小数点以下の.00の有無でエラーになったのでこちらは.00を除外するためにintにしてからstringに変換
            (string)((int)$mstUnit->getMoveSpeed()),
            $mstUnit->getSummonCost(),
            $mstUnit->getSummonCoolTime(),
            $mstUnit->getDamageKnockBackCount(),
            $targetSpecialMstAttackId,
            $mstAttack->getAttackDelay(),
            $mstAttack->getNextAttackInterval(),
            $mstUnit->getMstUnitAbility1(),
            $mstUnit->getMstUnitAbility2(),
            // ability3開放ランクに達していないので空文字
            "",
        ));

        // Exercise
        $this->service->checkMasterDataStatusMismatch(
            $usrUserId,
            $targetId,
            $contentType->value,
            $now,
            $partyStatuses,
            1,
            "",
            collect([])
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNull($logSuspectedUser);
    }

    public static function params_testCheckMasterDataStatusMismatch_チート検出なし()
    {
        return [
            '降臨バトル' => [CheatContentType::ADVENT_BATTLE],
            'ランクマッチ' => [CheatContentType::PVP],
        ];
    }

    #[DataProvider('params_testCheckMasterDataStatusMismatch_チート検出なし')]
    public function testCheckMasterDataStatusMismatch_チート検出なし(CheatContentType $cheatContentType)
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::MASTER_DATA_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1', 'ability_unlock_rank2' => 1, 'ability_unlock_rank3' => 1],
            ['id' => 'unit2', 'ability_unlock_rank2' => 1, 'ability_unlock_rank3' => 1],
            ['id' => 'unit3', 'ability_unlock_rank2' => 1, 'ability_unlock_rank3' => 1],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
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
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1003', 'mst_unit_id' => 'unit3', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
            ['usr_user_id' => $usrUserId, 'party_no' => 3, 'usr_unit_id_1' => 'usrUnit3', 'usr_unit_id_2' => 'usrUnit1', 'usr_unit_id_3' => 'usrUnit2'],
        ]);

        $createUnits = [
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
            ['id' => 'usrUnit3', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit3'],
        ];
        $usrUnits = UsrUnit::factory()->createMany($createUnits);

        $mstUnitEncyclopediaRewardId = 'unitEncyclopediaReward1';
        $eventBonusGroupId = 'eventBonusGroupId10';
        MstUnitEncyclopediaReward::factory()->create([
            'id' => $mstUnitEncyclopediaRewardId,
            'unit_encyclopedia_rank' => 1
        ]);
        $mstUnitEncyclopediaEffect = MstUnitEncyclopediaEffect::factory()->create([
            'mst_unit_encyclopedia_reward_id' => $mstUnitEncyclopediaRewardId,
            'effect_type' => EncyclopediaEffectType::HP->value,
            'value' => 100,
        ]);
        MstEventBonusUnit::factory()->create([
            'mst_unit_id' => 'unit2',
            'bonus_percentage' => 100,
            'event_bonus_group_id' => $eventBonusGroupId,
        ]);
        $paramList = [
            'unit1' => [2, 1],
            'unit2' => [4, 2],
            'unit3' => [2, 1],
        ];
        // ユーザの図鑑ランクを取得するためにUsrUnitSummaryを作成
        UsrUnitSummary::factory()->create([
            'usr_user_id' => $usrUserId,
            'grade_level_total_count' => count($createUnits),
        ]);

        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            /** @var UsrUnit $usrUnit */
            /** @var MstUnitEntity $mstUnit */
            /** @var MstAttackEntity $mstAttack */
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                $paramList[$usrUnit->getMstUnitId()][0],
                $paramList[$usrUnit->getMstUnitId()][1],
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // Exercise
        $this->service->checkMasterDataStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            3,
            $eventBonusGroupId,
            collect([$mstUnitEncyclopediaEffect->id])
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNull($logSuspectedUser);
    }

    public static function params_testCheckMasterDataStatusMismatch_チート検出あり()
    {
        return [
            '降臨バトル' => [CheatContentType::ADVENT_BATTLE],
            'ランクマッチ' => [CheatContentType::PVP],
        ];
    }

    #[DataProvider('params_testCheckMasterDataStatusMismatch_チート検出あり')]
    public function testCheckMasterDataStatusMismatch_チート検出あり(CheatContentType $cheatContentType)
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::MASTER_DATA_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
            ['id' => 'unit3'],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
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
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1003', 'mst_unit_id' => 'unit3', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1'],
            ['usr_user_id' => $usrUserId, 'party_no' => 2, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2'],
            ['usr_user_id' => $usrUserId, 'party_no' => 3, 'usr_unit_id_1' => 'usrUnit3', 'usr_unit_id_2' => 'usrUnit1', 'usr_unit_id_3' => 'usrUnit2'],
        ]);
        $usrUnits = UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
            ['id' => 'usrUnit3', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit3'],
        ]);

        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            /** @var UsrUnit $usrUnit */
            /** @var MstUnitEntity $mstUnit */
            /** @var MstAttackEntity $mstAttack */
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // Exercise
        $this->service->checkMasterDataStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            2,
            1,
            collect()
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        /** @var LogSuspectedUser $logSuspectedUser */
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($cheatContentType->value, $logSuspectedUser->content_type);
        $this->assertEquals(CheatType::MASTER_DATA_STATUS_MISMATCH->value, $logSuspectedUser->cheat_type);
        $this->assertEquals($targetId, $logSuspectedUser->target_id);
        $this->assertNotEmpty($logSuspectedUser->detail);
    }

    public static function params_testCheckMasterDataStatusMismatch_パラメータ別にチート検出()
    {
        $basePartyStatusParams = [
            'usrUnitId' => 'usrUnit1',
            'mstUnitId' => 'unit1',
            'color' => UnitColorType::COLORLESS->value,
            'roleType' => RoleType::ATTACK->value,
            'hp' => 1,
            'atk' => 1,
            'moveSpeed' => '1.11',
            'summonCost' => 1,
            'summonCoolTime' => 1,
            'damageKnockBackCount' => 1,
            'specialAttackMstAttackId' => '1001',
            'attackDelay' => 1,
            'nextAttackInterval' => 1,
            'mstUnitAbility1' => '1',
            'mstUnitAbility2' => '2',
            'mstUnitAbility3' => '3',
        ];
        $testCases = [
            '属性' => ['color' => UnitColorType::BLUE->value],
            'ロール' => ['roleType' => RoleType::DEFENSE->value],
            // 正常値(1) + 許容誤差(5) + 1
            '体力' => ['hp' => 7],
            '攻撃力' => ['atk' => 7],
            '移動速度' => ['moveSpeed' => 2],
            '召喚コスト' => ['summonCost' => 2],
            '召喚クールタイム' => ['summonCoolTime' => 2],
            'ダメージノックバック回数' => ['damageKnockBackCount' => 2],
            '攻撃ID' => ['specialAttackMstAttackId' => '1002'],
            '攻撃遅延' => ['attackDelay' => 2],
            '次の攻撃間隔' => ['nextAttackInterval' => 2],
            'ユニットアビリティ1' => ['mstUnitAbility1' => '2'],
            'ユニットアビリティ2' => ['mstUnitAbility2' => '3'],
            'ユニットアビリティ3' => ['mstUnitAbility3' => '4'],
        ];
        $testData = [];
        foreach ($testCases as $caseName => $params)
        {
            $partyStatusParams = array_merge($basePartyStatusParams, $params);
            $partyStatus = new PartyStatus(
                $partyStatusParams['usrUnitId'],
                $partyStatusParams['mstUnitId'],
                $partyStatusParams['color'],
                $partyStatusParams['roleType'],
                $partyStatusParams['hp'],
                $partyStatusParams['atk'],
                $partyStatusParams['moveSpeed'],
                $partyStatusParams['summonCost'],
                $partyStatusParams['summonCoolTime'],
                $partyStatusParams['damageKnockBackCount'],
                $partyStatusParams['specialAttackMstAttackId'],
                $partyStatusParams['attackDelay'],
                $partyStatusParams['nextAttackInterval'],
                $partyStatusParams['mstUnitAbility1'],
                $partyStatusParams['mstUnitAbility2'],
                $partyStatusParams['mstUnitAbility3'],
            );
            $testData[$caseName] = [$partyStatus, array_keys($params)[0]];
        }

        return $testData;
    }

    #[DataProvider('params_testCheckMasterDataStatusMismatch_パラメータ別にチート検出')]
    public function testCheckMasterDataStatusMismatch_パラメータ別にチート検出(PartyStatus $partyStatus, string $paramName)
    {
        // Setup
        $cheatContentType = CheatContentType::ADVENT_BATTLE;
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        $mstUnitId = 'unit1';
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::MASTER_DATA_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        MstUnit::factory()->create(['id' => $mstUnitId, 'move_speed' => '1.11', 'ability_unlock_rank2' => 1, 'ability_unlock_rank3' => 1]);
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
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
        ]);
        $grade = 1;
        MstAttack::factory()->createMany([
            [
                'id' => '1000',
                'mst_unit_id' => $mstUnitId,
                'attack_kind' => AttackKind::NORMAL->value,
            ],
            [
                'id' => '1001',
                'mst_unit_id' => $mstUnitId,
                'attack_kind' => AttackKind::SPECIAL->value,
                'unit_grade' => $grade,
            ],
        ]);
        UsrUnit::factory()->create([
            'id' => 'usrUnit1',
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => $mstUnitId,
            'grade_level' => $grade,
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'party_no' => 1,
            'usr_unit_id_1' => 'usrUnit1'
        ]);

        $partyStatuses = collect();
        $partyStatuses->push($partyStatus);

        // Exercise
        $this->service->checkMasterDataStatusMismatch(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            1,
            1,
            collect(),
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        /** @var LogSuspectedUser $logSuspectedUser */
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($cheatContentType->value, $logSuspectedUser->content_type);
        $this->assertEquals(CheatType::MASTER_DATA_STATUS_MISMATCH->value, $logSuspectedUser->cheat_type);
        $this->assertEquals($targetId, $logSuspectedUser->target_id);
        $this->assertStringContainsString("[{$paramName}]", $logSuspectedUser->detail);
        // 他のケースでチート検出されないことを確認(「|」が含まれていなければOK)
        $this->assertStringNotContainsString("|", $logSuspectedUser->detail);
    }

    public function test_checkBattleStatusMismatchWithOpponent_チート検出なし()
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        $cheatContentType = CheatContentType::PVP;
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
        MstUnitLevelUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'level' => 1, 'required_coin' => 1],
            ['unit_label' => $unitLabel, 'level' => 2, 'required_coin' => 2],
            ['unit_label' => $unitLabel, 'level' => 3, 'required_coin' => 3],
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2',
        ]);
        $usrUnits = UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
        ]);

        // 自分のパーティステータスを作成（バトル前後で不変）
        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // 自分の原画パーティステータスを作成（バトル前後で不変）
        $artworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_1', 1),
            new ArtworkPartyStatus('artwork_2', 1),
        ]);

        // 対戦相手のパーティステータスを作成（バトル前後で不変）
        $opponentPartyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $opponentPartyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // 対戦相手の原画パーティステータスを作成（バトル前後で不変）
        $opponentArtworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_3', 1),
            new ArtworkPartyStatus('artwork_4', 1),
        ]);

        // CheatSessionPartyWithOpponent形式でセッションデータを作成
        $sessionData = new \App\Domain\Cheat\Entities\CheatSessionPartyWithOpponent(
            $partyStatuses->map(fn($p) => $p->formatToLog()),
            $artworkPartyStatus->map(fn($a) => $a->formatToLog()),
            $opponentPartyStatuses->map(fn($p) => $p->formatToLog()),
            $opponentArtworkPartyStatus->map(fn($a) => $a->formatToLog())
        );

        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => $cheatContentType->value,
            'target_id' => $targetId,
            'party_status' => $sessionData->toJson(),
        ]);

        // Exercise
        $this->service->checkBattleStatusMismatchWithOpponent(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            $artworkPartyStatus,
            $opponentPartyStatuses,
            $opponentArtworkPartyStatus,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNull($logSuspectedUser);
    }

    public function test_checkBattleStatusMismatchWithOpponent_自分のパーティ不一致_チート検出あり()
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        $cheatContentType = CheatContentType::PVP;
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
        MstUnitLevelUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'level' => 1, 'required_coin' => 1],
            ['unit_label' => $unitLabel, 'level' => 2, 'required_coin' => 2],
            ['unit_label' => $unitLabel, 'level' => 3, 'required_coin' => 3],
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2',
        ]);
        $usrUnits = UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
        ]);

        // 自分のパーティステータス（バトル前後で不変）
        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // バトル開始時の自分の原画パーティステータス
        $beforeBattleArtworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_1', 1),
            new ArtworkPartyStatus('artwork_2', 1),
        ]);

        // バトル終了時の自分の原画パーティステータス（変更後）
        $afterBattleArtworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_changed', 1),
            new ArtworkPartyStatus('artwork_2', 1),
        ]);

        // 対戦相手のパーティステータス（バトル前後で不変）
        $opponentPartyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $opponentPartyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // 対戦相手の原画パーティステータス（バトル前後で不変）
        $opponentArtworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_3', 1),
            new ArtworkPartyStatus('artwork_4', 1),
        ]);

        // CheatSessionPartyWithOpponent形式でセッションデータを作成（バトル開始時のデータ）
        $sessionData = new \App\Domain\Cheat\Entities\CheatSessionPartyWithOpponent(
            $partyStatuses->map(fn($p) => $p->formatToLog()),
            $beforeBattleArtworkPartyStatus->map(fn($a) => $a->formatToLog()),
            $opponentPartyStatuses->map(fn($p) => $p->formatToLog()),
            $opponentArtworkPartyStatus->map(fn($a) => $a->formatToLog())
        );

        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => $cheatContentType->value,
            'target_id' => $targetId,
            'party_status' => $sessionData->toJson(),
        ]);

        // Exercise - バトル終了時は自分の原画パーティが変更されている
        $this->service->checkBattleStatusMismatchWithOpponent(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            $afterBattleArtworkPartyStatus, // バトル後の変更されたデータ
            $opponentPartyStatuses,
            $opponentArtworkPartyStatus,
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify — 自分の原画パーティが不一致のためチート検出
        /** @var LogSuspectedUser $logSuspectedUser */
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($cheatContentType->value, $logSuspectedUser->content_type);
        $this->assertEquals(CheatType::BATTLE_STATUS_MISMATCH->value, $logSuspectedUser->cheat_type);
        $this->assertEquals($targetId, $logSuspectedUser->target_id);
        $this->assertNotEmpty($logSuspectedUser->detail);
    }

    public function test_checkBattleStatusMismatchWithOpponent_対戦相手のパーティ不一致_チート検出あり()
    {
        // Setup
        $targetId = fake()->uuid();
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();
        $unitLabel = 'DropR';
        $cheatContentType = CheatContentType::PVP;
        MstCheatSetting::factory()->create([
            'content_type' => $cheatContentType->value,
            'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
            'start_at' => $now->subDay()->toDateTimeString(),
            'end_at' => $now->addDay()->toDateTimeString(),
        ]);

        $mstUnits = MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
        ])->map(
            fn($mstUnit) => $mstUnit->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getId();
        });
        MstUnitLevelUp::factory()->createMany([
            ['unit_label' => $unitLabel, 'level' => 1, 'required_coin' => 1],
            ['unit_label' => $unitLabel, 'level' => 2, 'required_coin' => 2],
            ['unit_label' => $unitLabel, 'level' => 3, 'required_coin' => 3],
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => $unitLabel, 'grade_level' => 1, 'coefficient' => 1],
            ['unit_label' => $unitLabel, 'grade_level' => 2, 'coefficient' => 1],
        ]);
        $mstAttacks = MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
            ['id' => '1002', 'mst_unit_id' => 'unit2', 'attack_kind' => AttackKind::SPECIAL->value],
        ])->map(
            fn($mstAttack) => $mstAttack->toEntity()
        )->keyBy(function ($entity) {
            return $entity->getMstUnitId();
        });
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId, 'party_no' => 1, 'usr_unit_id_1' => 'usrUnit1', 'usr_unit_id_2' => 'usrUnit2',
        ]);
        $usrUnits = UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
            ['id' => 'usrUnit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2'],
        ]);

        // 自分のパーティステータス（バトル前後で不変）
        $partyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $partyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // 自分の原画パーティステータス（バトル前後で不変）
        $artworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_1', 1),
            new ArtworkPartyStatus('artwork_2', 1),
        ]);

        // 対戦相手のパーティステータス（バトル前後で不変）
        $opponentPartyStatuses = collect();
        foreach ($usrUnits as $usrUnit)
        {
            $mstUnit = $mstUnits->get($usrUnit->getMstUnitId());
            $mstAttack = $mstAttacks->get($usrUnit->getMstUnitId());
            $opponentPartyStatuses->push(new PartyStatus(
                $usrUnit->getId(),
                $usrUnit->getMstUnitId(),
                $mstUnit->getColor(),
                $mstUnit->getRoleType(),
                1,
                1,
                $mstUnit->getMoveSpeed(),
                $mstUnit->getSummonCost(),
                $mstUnit->getSummonCoolTime(),
                $mstUnit->getDamageKnockBackCount(),
                $mstAttack->getId(),
                $mstAttack->getAttackDelay(),
                $mstAttack->getNextAttackInterval(),
                $mstUnit->getMstUnitAbility1(),
                $mstUnit->getMstUnitAbility2(),
                $mstUnit->getMstUnitAbility3(),
            ));
        }

        // バトル開始時の対戦相手の原画パーティステータス
        $beforeBattleOpponentArtworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_3', 1),
            new ArtworkPartyStatus('artwork_4', 1),
        ]);

        // バトル終了時の対戦相手の原画パーティステータス（変更後）
        $afterBattleOpponentArtworkPartyStatus = collect([
            new ArtworkPartyStatus('artwork_opponent_changed', 1),
            new ArtworkPartyStatus('artwork_4', 1),
        ]);

        // CheatSessionPartyWithOpponent形式でセッションデータを作成（バトル開始時のデータ）
        $sessionData = new \App\Domain\Cheat\Entities\CheatSessionPartyWithOpponent(
            $partyStatuses->map(fn($p) => $p->formatToLog()),
            $artworkPartyStatus->map(fn($a) => $a->formatToLog()),
            $opponentPartyStatuses->map(fn($p) => $p->formatToLog()),
            $beforeBattleOpponentArtworkPartyStatus->map(fn($a) => $a->formatToLog())
        );

        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => $cheatContentType->value,
            'target_id' => $targetId,
            'party_status' => $sessionData->toJson(),
        ]);

        // Exercise - バトル終了時は対戦相手の原画パーティが変更されている
        $this->service->checkBattleStatusMismatchWithOpponent(
            $usrUserId,
            $targetId,
            $cheatContentType->value,
            $now,
            $partyStatuses,
            $artworkPartyStatus,
            $opponentPartyStatuses,
            $afterBattleOpponentArtworkPartyStatus, // バトル後の変更されたデータ
        );
        $this->saveAll();
        $this->saveAllLogModel();

        // Verify — 対戦相手の原画パーティが不一致のためチート検出
        /** @var LogSuspectedUser $logSuspectedUser */
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($cheatContentType->value, $logSuspectedUser->content_type);
        $this->assertEquals(CheatType::BATTLE_STATUS_MISMATCH->value, $logSuspectedUser->cheat_type);
        $this->assertEquals($targetId, $logSuspectedUser->target_id);
        $this->assertNotEmpty($logSuspectedUser->detail);
    }

}
