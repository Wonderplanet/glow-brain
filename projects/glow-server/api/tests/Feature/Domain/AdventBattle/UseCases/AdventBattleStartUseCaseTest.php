<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\Models\UsrAdventBattleSessionInterface;
use App\Domain\AdventBattle\UseCases\AdventBattleStartUseCase;
use App\Domain\Campaign\Enums\CampaignTargetType;
use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Cheat\Enums\CheatContentType;
use App\Domain\Cheat\Enums\CheatType;
use App\Domain\Cheat\Models\LogSuspectedUser;
use App\Domain\Cheat\Models\UsrCheatSession;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Enums\EncyclopediaEffectType;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAttack;
use App\Domain\Resource\Mst\Models\MstCheatSetting;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaReward;
use App\Domain\Resource\Mst\Models\MstUnitGradeCoefficient;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\OprCampaign;
use App\Domain\Unit\Enums\AttackKind;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\Unit\Models\UsrUnitSummary;
use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaReward;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class AdventBattleStartUseCaseTest extends TestCase
{
    use TestMissionTrait;

    private AdventBattleStartUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(AdventBattleStartUseCase::class);
    }

    private function createMstTestData(): void
    {
        MstAdventBattle::factory()->createMany([
            [
                'id' => '10',
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
            ],
            [
                'id' => '11',
                'advent_battle_type' => AdventBattleType::RAID->value,
                'start_at' => '2024-03-01 00:00:00',
                'end_at' => '2024-06-01 00:00:00',
            ],
        ]);
        MstCheatSetting::factory()->createMany([
            [
                'content_type' => CheatContentType::ADVENT_BATTLE->value,
                'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
            ],
            // [
            //     'content_type' => CheatContentType::ADVENT_BATTLE->value,
            //     'cheat_type' => CheatType::MASTER_DATA_STATUS_MISMATCH->value,
            //     'is_excluded_ranking' => true,
            //     'start_at' => '2024-01-01 00:00:00',
            //     'end_at' => '2024-03-01 00:00:00',
            // ],
        ]);
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => 'DropR', 'grade_level' => 1, 'coefficient' => 1],
        ]);
    }

    #[DataProvider('params_exec_正常実行')]
    public function test_exec_正常実行(bool $isChallengeAd)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-01-10 00:00:00');
        $currentUser = new CurrentUser($usrUserId);

        $mstAdventBattleId = "10";
        $partyNo = 3;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        MstUnit::factory()->createMany([
            [
                'id' => 'unit1',
                'color' => 'Red',
                'mst_unit_ability_id1' => '2001',
                'ability_unlock_rank1' => 1,
                'mst_unit_ability_id2' => '3001',
                'ability_unlock_rank2' => 1,
                'mst_unit_ability_id3' => '4001',
                'ability_unlock_rank3' => 1,
                'move_speed' => '1.11',
            ],
        ]);
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
        MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1'],
        ]);
        $inGameBattleLog =[
            'partyStatus' => [
                [
                    'usrUnitId' => 'usrUnit1',
                    'mstUnitId' => 'unit1',
                    'color' => 'Red',
                    'roleType' => 'Attack',
                    'hp' => 1,
                    'atk' => 1,
                    'moveSpeed' => '1.11',
                    'summonCost' => 1,
                    'summonCoolTime' => 1,
                    'damageKnockBackCount' => 1,
                    'specialAttackMstAttackId' => '1001',
                    'attackDelay' => 1,
                    'nextAttackInterval' => 1,
                    'mstUnitAbility1' => '2001',
                    'mstUnitAbility2' => '3001',
                    'mstUnitAbility3' => '4001',
                ]
            ],
        ];

        // Exercise
        $this->useCase->exec($currentUser, $mstAdventBattleId, $partyNo, $isChallengeAd, $inGameBattleLog);

        // Verify
        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUserId)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($usrUserId, $usrAdventBattle->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        $this->assertEquals(1, $usrAdventBattle->getChallengeCount());
        $this->assertFalse($usrAdventBattle->isExcludedRanking());
        if ($isChallengeAd) {
            $this->assertEquals(0, $usrAdventBattle->getResetChallengeCount());
            $this->assertEquals(1, $usrAdventBattle->getResetAdChallengeCount());
        } else {
            $this->assertEquals(1, $usrAdventBattle->getResetChallengeCount());
            $this->assertEquals(0, $usrAdventBattle->getResetAdChallengeCount());
        }

        /** @var UsrAdventBattleSessionInterface $usrAdventBattleSession */
        $usrAdventBattleSession = UsrAdventBattleSession::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrUserId, $usrAdventBattleSession->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattleSession->getMstAdventBattleId());
        $this->assertEquals($partyNo, $usrAdventBattleSession->getPartyNo());

        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNull($logSuspectedUser);

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }

    public static function params_exec_正常実行()
    {
        return [
            '広告なし' => [false],
            '広告あり' => [true],
        ];
    }

    #[DataProvider('params_exec_2回目')]
    public function test_exec_2回目(bool $isChallengeAd)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-01-10 00:00:00');
        $currentUser = new CurrentUser($usrUserId);

        $mstAdventBattleId = "10";
        $partyNo = 3;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        MstUnit::factory()->createMany([
            [
                'id' => 'unit1',
                'color' => 'Red',
                'mst_unit_ability_id1' => '2001',
                'ability_unlock_rank1' => 1,
                'mst_unit_ability_id2' => '3001',
                'ability_unlock_rank2' => 1,
                'mst_unit_ability_id3' => '4001',
                'ability_unlock_rank3' => 1,
                'move_speed' => '1.11',
            ],
        ]);
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
        MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'battle_count' => 1],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1'],
        ]);
        $inGameBattleLog =[
            'partyStatus' => [
                [
                    'usrUnitId' => 'usrUnit1',
                    'mstUnitId' => 'unit1',
                    'color' => 'Red',
                    'roleType' => 'Attack',
                    'hp' => 1,
                    'atk' => 1,
                    'moveSpeed' => '1.11',
                    'summonCost' => 1,
                    'summonCoolTime' => 1,
                    'damageKnockBackCount' => 1,
                    'specialAttackMstAttackId' => '1001',
                    'attackDelay' => 1,
                    'nextAttackInterval' => 1,
                    'mstUnitAbility1' => '2001',
                    'mstUnitAbility2' => '3001',
                    'mstUnitAbility3' => '4001',
                ]
            ],
        ];

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'challenge_count' => 2,
            'reset_challenge_count' => 1,
            'reset_ad_challenge_count' => 1,
            'latest_reset_at' => $now->setHour(1)->toDateTimeString(),
        ]);
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => "20",
            'party_no' => 5,
        ]);

        // Exercise
        $this->useCase->exec($currentUser, $mstAdventBattleId, $partyNo, $isChallengeAd, $inGameBattleLog);

        // DB確認
        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $currentUser->getId())
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($currentUser->getId(), $usrAdventBattle->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($now->setHour(1)->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        $this->assertEquals(3, $usrAdventBattle->getChallengeCount());
        $this->assertFalse($usrAdventBattle->isExcludedRanking());
        if ($isChallengeAd) {
            $this->assertEquals(1, $usrAdventBattle->getResetChallengeCount());
            $this->assertEquals(2, $usrAdventBattle->getResetAdChallengeCount());
        } else {
            $this->assertEquals(2, $usrAdventBattle->getResetChallengeCount());
            $this->assertEquals(1, $usrAdventBattle->getResetAdChallengeCount());
        }

        /** @var UsrAdventBattleSessionInterface $usrAdventBattleSession */
        $usrAdventBattleSession = UsrAdventBattleSession::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrUserId, $usrAdventBattleSession->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattleSession->getMstAdventBattleId());
        $this->assertEquals($partyNo, $usrAdventBattleSession->getPartyNo());

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(2, $usrUnit->getBattleCount());
        }
    }

    public static function params_exec_2回目()
    {
        return [
            '広告なし' => [false],
            '広告あり' => [true],
        ];
    }

    #[DataProvider('params_exec_チャレンジ回数上限')]
    public function test_exec_チャレンジ回数上限(bool $isChallengeAd)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-01-10 00:00:00');
        $currentUser = new CurrentUser($usrUserId);

        $mstAdventBattleId = "10";
        $partyNo = 3;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        MstUnit::factory()->createMany([
            [
                'id' => 'unit1',
                'color' => 'Red',
                'mst_unit_ability_id1' => '2001',
                'ability_unlock_rank1' => 1,
                'mst_unit_ability_id2' => '3001',
                'ability_unlock_rank2' => 1,
                'mst_unit_ability_id3' => '4001',
                'ability_unlock_rank3' => 1,
                'move_speed' => '1.11',
            ],
        ]);
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
        MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1'],
        ]);
        $inGameBattleLog =[
            'partyStatus' => [
                [
                    'usrUnitId' => 'usrUnit1',
                    'mstUnitId' => 'unit1',
                    'color' => 'Red',
                    'roleType' => 'Attack',
                    'hp' => 1,
                    'atk' => 1,
                    'moveSpeed' => '1.11',
                    'summonCost' => 1,
                    'summonCoolTime' => 1,
                    'damageKnockBackCount' => 1,
                    'specialAttackMstAttackId' => '1001',
                    'attackDelay' => 1,
                    'nextAttackInterval' => 1,
                    'mstUnitAbility1' => '2001',
                    'mstUnitAbility2' => '3001',
                    'mstUnitAbility3' => '4001',
                ]
            ],
        ];

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'challenge_count' => 15,
            'reset_challenge_count' => 5,
            'reset_ad_challenge_count' => 10,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ADVENT_BATTLE_CANNOT_START);
        $this->expectExceptionMessage(
            $isChallengeAd ? 'ad challengeable count is over' : 'challengeable count is over'
        );
        $this->useCase->exec($currentUser, $mstAdventBattleId, $partyNo, $isChallengeAd, $inGameBattleLog);
    }

    public static function params_exec_チャレンジ回数上限()
    {
        return [
            '広告なし' => [false],
            '広告あり' => [true],
        ];
    }

    #[DataProvider('params_exec_キャンペーン')]
    public function test_exec_キャンペーン(bool $isChallengeAd)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-01-10 00:00:00');
        $currentUser = new CurrentUser($usrUserId);

        $mstAdventBattleId = "10";
        $partyNo = 3;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        MstUnit::factory()->createMany([
            [
                'id' => 'unit1',
                'color' => 'Red',
                'mst_unit_ability_id1' => '2001',
                'ability_unlock_rank1' => 1,
                'mst_unit_ability_id2' => '3001',
                'ability_unlock_rank2' => 1,
                'mst_unit_ability_id3' => '4001',
                'ability_unlock_rank3' => 1,
                'move_speed' => '1.11',
            ],
        ]);
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
        MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1'],
        ]);
        $inGameBattleLog =[
            'partyStatus' => [
                [
                    'usrUnitId' => 'usrUnit1',
                    'mstUnitId' => 'unit1',
                    'color' => 'Red',
                    'roleType' => 'Attack',
                    'hp' => 1,
                    'atk' => 1,
                    'moveSpeed' => '1.11',
                    'summonCost' => 1,
                    'summonCoolTime' => 1,
                    'damageKnockBackCount' => 1,
                    'specialAttackMstAttackId' => '1001',
                    'attackDelay' => 1,
                    'nextAttackInterval' => 1,
                    'mstUnitAbility1' => '2001',
                    'mstUnitAbility2' => '3001',
                    'mstUnitAbility3' => '4001',
                ]
            ],
        ];

        OprCampaign::factory()->create([
            'campaign_type' => CampaignType::CHALLENGE_COUNT->value,
            'target_type' => CampaignTargetType::ADVENT_BATTLE->value,
            'effect_value' => 1,
            // バグ修正確認 null想定で空文字が入り不具合が起きたので正常に動くことを確認するために空文字をいれる
            'target_id' => "",
        ])->toEntity();
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'challenge_count' => 15,
            'reset_challenge_count' => 5,
            'reset_ad_challenge_count' => 10,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => "20",
            'party_no' => 5,
        ]);

        // Exercise
        if ($isChallengeAd) {
            // 広告ありの挑戦回数にキャンペーンが影響しない事を確認
            $this->expectException(GameException::class);
            $this->expectExceptionCode(ErrorCode::ADVENT_BATTLE_CANNOT_START);
            $this->expectExceptionMessage('ad challengeable count is over');
        }
        $this->useCase->exec($currentUser, $mstAdventBattleId, $partyNo, $isChallengeAd, $inGameBattleLog);

        // DB確認
        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $currentUser->getId())
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($currentUser->getId(), $usrAdventBattle->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        $this->assertEquals(16, $usrAdventBattle->getChallengeCount());
        $this->assertFalse($usrAdventBattle->isExcludedRanking());
        $this->assertEquals(6, $usrAdventBattle->getResetChallengeCount());
        $this->assertEquals(10, $usrAdventBattle->getResetAdChallengeCount());

        /** @var UsrAdventBattleSessionInterface $usrAdventBattleSession */
        $usrAdventBattleSession = UsrAdventBattleSession::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrUserId, $usrAdventBattleSession->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattleSession->getMstAdventBattleId());
        $this->assertEquals($partyNo, $usrAdventBattleSession->getPartyNo());

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }

    public static function params_exec_キャンペーン()
    {
        return [
            '広告なし' => [false],
            '広告あり' => [true],
        ];
    }

    #[DataProvider('params_exec_リセット')]
    public function test_exec_リセット(bool $isChallengeAd)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-01-10 00:00:00');
        $currentUser = new CurrentUser($usrUserId);

        $mstAdventBattleId = "10";
        $partyNo = 3;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        MstUnit::factory()->createMany([
            [
                'id' => 'unit1',
                'color' => 'Red',
                'mst_unit_ability_id1' => '2001',
                'ability_unlock_rank1' => 1,
                'mst_unit_ability_id2' => '3001',
                'ability_unlock_rank2' => 1,
                'mst_unit_ability_id3' => '4001',
                'ability_unlock_rank3' => 1,
                'move_speed' => '1.11',
            ],
        ]);
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
        MstAttack::factory()->createMany([
            ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1'],
        ]);
        $inGameBattleLog =[
            'partyStatus' => [
                [
                    'usrUnitId' => 'usrUnit1',
                    'mstUnitId' => 'unit1',
                    'color' => 'Red',
                    'roleType' => 'Attack',
                    'hp' => 1,
                    'atk' => 1,
                    'moveSpeed' => '1.11',
                    'summonCost' => 1,
                    'summonCoolTime' => 1,
                    'damageKnockBackCount' => 1,
                    'specialAttackMstAttackId' => '1001',
                    'attackDelay' => 1,
                    'nextAttackInterval' => 1,
                    'mstUnitAbility1' => '2001',
                    'mstUnitAbility2' => '3001',
                    'mstUnitAbility3' => '4001',
                ]
            ],
        ];

        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'challenge_count' => 2,
            'reset_challenge_count' => 1,
            'reset_ad_challenge_count' => 1,
            'latest_reset_at' => $now->subDay()->toDateTimeString(),
        ]);

        // Exercise
        $this->useCase->exec($currentUser, $mstAdventBattleId, $partyNo, $isChallengeAd, $inGameBattleLog);

        // DB確認
        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $currentUser->getId())
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($currentUser->getId(), $usrAdventBattle->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        $this->assertEquals(3, $usrAdventBattle->getChallengeCount());
        $this->assertFalse($usrAdventBattle->isExcludedRanking());
        if ($isChallengeAd) {
            $this->assertEquals(0, $usrAdventBattle->getResetChallengeCount());
            $this->assertEquals(1, $usrAdventBattle->getResetAdChallengeCount());
        } else {
            $this->assertEquals(1, $usrAdventBattle->getResetChallengeCount());
            $this->assertEquals(0, $usrAdventBattle->getResetAdChallengeCount());
        }

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }

    public static function params_exec_リセット()
    {
        return [
            '広告なし' => [false],
            '広告あり' => [true],
        ];
    }

    /**
     * MasterDataStatusMismatchでの誤検出が多発しているので、MasterDataStatusMismatchのチートチェックを無効化した。
     * その結果、advent_battle/start ではチートチェック処理がなくなったため、テストを無効化する。
     */
    // public function test_exec_チート検出()
    // {
    //     // Setup
    //     $usrUserId = $this->createUsrUser()->getId();
    //     $now = $this->fixTime('2024-01-10 00:00:00');
    //     $currentUser = new CurrentUser($usrUserId);
    //     $isChallengeAd = true;

    //     $mstAdventBattleId = "10";
    //     $partyNo = 3;
    //     $unitLabel = 'DropR';
    //     $this->createMstTestData();
    //     MstUnit::factory()->createMany([
    //         [
    //             'id' => 'unit1',
    //             'color' => 'Red',
    //             'mst_unit_ability_id1' => '2001',
    //             'mst_unit_ability_id2' => '3001',
    //             'mst_unit_ability_id3' => '4001',
    //             'move_speed' => '1.11',
    //         ],
    //     ]);
    //     MstUnitLevelUp::factory()->createMany([
    //         [
    //             'unit_label' => $unitLabel,
    //             'level' => 1,
    //             'required_coin' => 1,
    //         ],
    //         [
    //             'unit_label' => $unitLabel,
    //             'level' => 2,
    //             'required_coin' => 2,
    //         ],
    //         [
    //             'unit_label' => $unitLabel,
    //             'level' => 3,
    //             'required_coin' => 3,
    //         ],
    //         [
    //             'unit_label' => $unitLabel,
    //             'level' => 4,
    //             'required_coin' => 4,
    //         ],
    //         [
    //             'unit_label' => $unitLabel,
    //             'level' => 5,
    //             'required_coin' => 5,
    //         ],
    //     ]);
    //     MstAttack::factory()->createMany([
    //         ['id' => '1001', 'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value],
    //     ]);
    //     UsrUnit::factory()->createMany([
    //         ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
    //     ]);
    //     UsrParty::factory()->createMany([
    //         ['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1'],
    //     ]);
    //     $inGameBattleLog =[
    //         'partyStatus' => [
    //             [
    //                 'usrUnitId' => 'usrUnit1',
    //                 'mstUnitId' => 'unit1',
    //                 'color' => 'Red',
    //                 'roleType' => 'Attack',
    //                 'hp' => 2,
    //                 'atk' => 1,
    //                 'moveSpeed' => '1.11',
    //                 'summonCost' => 1,
    //                 'summonCoolTime' => 1,
    //                 'damageKnockBackCount' => 1,
    //                 'specialAttackMstAttackId' => '1001',
    //                 'attackDelay' => 1,
    //                 'nextAttackInterval' => 1,
    //                 'mstUnitAbility1' => '2001',
    //                 'mstUnitAbility2' => '3001',
    //                 'mstUnitAbility3' => '4001',
    //             ]
    //         ],
    //     ];

    //     // Exercise
    //     $this->useCase->exec($currentUser, $mstAdventBattleId, $partyNo, $isChallengeAd, $inGameBattleLog);

    //     // Verify
    //     /** @var UsrAdventBattleInterface $usrAdventBattle */
    //     $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUserId)
    //         ->where('mst_advent_battle_id', $mstAdventBattleId)
    //         ->first();
    //     $this->assertEquals($usrUserId, $usrAdventBattle->getUsrUserId());
    //     $this->assertEquals($mstAdventBattleId, $usrAdventBattle->getMstAdventBattleId());
    //     $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
    //     $this->assertEquals(1, $usrAdventBattle->getChallengeCount());
    //     $this->assertTrue($usrAdventBattle->isExcludedRanking());
    //     if ($isChallengeAd) {
    //         $this->assertEquals(0, $usrAdventBattle->getResetChallengeCount());
    //         $this->assertEquals(1, $usrAdventBattle->getResetAdChallengeCount());
    //     } else {
    //         $this->assertEquals(1, $usrAdventBattle->getResetChallengeCount());
    //         $this->assertEquals(0, $usrAdventBattle->getResetAdChallengeCount());
    //     }

    //     /** @var UsrAdventBattleSessionInterface $usrAdventBattleSession */
    //     $usrAdventBattleSession = UsrAdventBattleSession::where('usr_user_id', $usrUserId)->first();
    //     $this->assertEquals($usrUserId, $usrAdventBattleSession->getUsrUserId());
    //     $this->assertEquals($mstAdventBattleId, $usrAdventBattleSession->getMstAdventBattleId());
    //     $this->assertEquals($partyNo, $usrAdventBattleSession->getPartyNo());

    //     /** @var UsrCheatSession $usrCheatSession */
    //     $usrCheatSession = UsrCheatSession::query()->where('usr_user_id', $usrUserId)->first();
    //     $this->assertNotNull($usrCheatSession);
    //     $this->assertEquals($usrUserId, $usrCheatSession->getUsrUserId());
    //     $this->assertEquals(CheatContentType::ADVENT_BATTLE->value, $usrCheatSession->getContentType());
    //     $this->assertEquals($mstAdventBattleId, $usrCheatSession->getTargetId());

    //     /** @var LogSuspectedUser $logSuspectedUser */
    //     $logSuspectedUsers = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->get();
    //     $this->assertEquals(1, $logSuspectedUsers->count());
    //     $logSuspectedUser = $logSuspectedUsers->get(0);
    //     $this->assertEquals(CheatContentType::ADVENT_BATTLE->value, $logSuspectedUser->content_type);
    //     $this->assertEquals(CheatType::MASTER_DATA_STATUS_MISMATCH->value, $logSuspectedUser->cheat_type);
    //     $this->assertEquals($mstAdventBattleId, $logSuspectedUser->target_id);
    //     $this->assertNotEmpty($logSuspectedUser->detail);

    //     $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
    //     foreach ($usrUnits as $usrUnit) {
    //         $this->assertEquals(1, $usrUnit->getBattleCount());
    //     }
    // }

    public function test_exec_チート誤検出修正確認()
    {
        // チート判定時の図鑑効果の適用が報酬を受け取ったデータのみではなく累計グレードで開放されているところまで適用されることを確認するためのテスト
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $this->fixTime('2024-01-10 00:00:00');
        $currentUser = new CurrentUser($usrUserId);
        $isChallengeAd = true;

        $mstAdventBattleId = "10";
        $partyNo = 3;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        $mstUnit = MstUnit::factory()->create([
            'id' => 'unit1',
            'color' => 'Red',
            'min_hp' => 100,
            'min_attack_power' => 100,
            'mst_unit_ability_id1' => '2001',
            'ability_unlock_rank1' => 1,
            'mst_unit_ability_id2' => '3001',
            'ability_unlock_rank2' => 1,
            'mst_unit_ability_id3' => '4001',
            'ability_unlock_rank3' => 1,
            'move_speed' => '1.11',
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
            ]
        ]);
        MstUnitEncyclopediaReward::factory()->createMany([
            ['id' => 'reward1', 'unit_encyclopedia_rank' => 5],
            ['id' => 'reward2', 'unit_encyclopedia_rank' => 10],
            ['id' => 'reward3', 'unit_encyclopedia_rank' => 15],
            ['id' => 'reward4', 'unit_encyclopedia_rank' => 20],
        ]);
        MstUnitEncyclopediaEffect::factory()->createMany([
            ['mst_unit_encyclopedia_reward_id' => 'reward1', 'effect_type' => EncyclopediaEffectType::HP->value, 'value' => 10],
            ['mst_unit_encyclopedia_reward_id' => 'reward2', 'effect_type' => EncyclopediaEffectType::HP->value, 'value' => 20],
            ['mst_unit_encyclopedia_reward_id' => 'reward3', 'effect_type' => EncyclopediaEffectType::ATTACK_POWER->value, 'value' => 10],
            ['mst_unit_encyclopedia_reward_id' => 'reward4', 'effect_type' => EncyclopediaEffectType::ATTACK_POWER->value, 'value' => 20],
        ]);
        $mstAttack = MstAttack::factory()->create([
            'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::NORMAL->value,
        ])->toEntity();
        $mstAttackSpecial = MstAttack::factory()->create([
            'mst_unit_id' => 'unit1', 'attack_kind' => AttackKind::SPECIAL->value, 'unit_grade' => 1,
        ])->toEntity();
        $usrUnit = UsrUnit::factory()->create([
            'id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => $mstUnit->getId(), 'grade_level' => 1,
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => $usrUnit->getId(),
        ]);
        UsrUnitSummary::factory()->create(['usr_user_id' => $usrUserId, 'grade_level_total_count' => 20]);
        // キャラ図鑑ランク効果は、報酬未受け取りでも発動するので、usr_received_unit_encyclopedia_rewardsレコードは作らない

        $inGameBattleLog = [
            'partyStatus' => [
                [
                    'usrUnitId' => $usrUnit->getId(),
                    'mstUnitId' => $mstUnit->getId(),
                    'color' => $mstUnit->getColor(),
                    'roleType' => $mstUnit->getRoleType(),
                    'hp' => (int)($mstUnit->getMinHp() * 1.3), // 図鑑効果で+30%される（reward1:+10% + reward2:+20%）
                    'atk' => (int)($mstUnit->getMinAttackPower() * 1.3), // 図鑑効果で+30%される（reward3:+10% + reward4:+20%）
                    'moveSpeed' => $mstUnit->getMoveSpeed(),
                    'summonCost' => $mstUnit->getSummonCost(),
                    'summonCoolTime' => $mstUnit->getSummonCoolTime(),
                    'damageKnockBackCount' => $mstUnit->getDamageKnockBackCount(),
                    'specialAttackMstAttackId' => $mstAttackSpecial->getId(),
                    'attackDelay' => $mstAttack->getAttackDelay(),
                    'nextAttackInterval' => $mstAttack->getNextAttackInterval(),
                    'mstUnitAbility1' => $mstUnit->getMstUnitAbility1(),
                    'mstUnitAbility2' => $mstUnit->getMstUnitAbility2(),
                    'mstUnitAbility3' => $mstUnit->getMstUnitAbility3(),
                ]
            ],
        ];

        // Exercise
        $this->useCase->exec($currentUser, $mstAdventBattleId, $partyNo, $isChallengeAd, $inGameBattleLog);

        // Verify
        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->get()->first();
        $this->assertNull($logSuspectedUser);
    }

    public function test_exec_ミッション進捗確認()
    {
        // 実装が漏れていたので確認するためのテストを追加
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-01-10 00:00:00');
        $currentUser = new CurrentUser($usrUserId);

        $mstAdventBattleId = "10";
        $partyNo = 1;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        $mstUnit = MstUnit::factory()->create([
            'id' => 'unit1',
            'color' => 'Red',
            'mst_unit_ability_id1' => '2001',
            'mst_unit_ability_id2' => '3001',
            'mst_unit_ability_id3' => '4001',
            'move_speed' => '1.11',
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
            ]
        ]);
        $specialAttackId = MstAttack::factory()->create([
            'id' => '1001',
            'mst_unit_id' => $mstUnit->getId(),
            'attack_kind' => AttackKind::SPECIAL->value
        ])->toEntity()->getId();
        $missionId = MstMissionLimitedTerm::factory()->create([
            'id' => 'challenge',
            'progress_group_key' => 'progress_1',
            'criterion_type' => MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT->value,
            'criterion_value' => null,
            'criterion_count' => 2,
            'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
        ])->toEntity()->getId();
        $usrUnitId = UsrUnit::factory()
            ->create(['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => $mstUnit->getId()])
            ->getId();
        UsrParty::factory()
            ->create(['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => $usrUnitId]);

        UsrMissionLimitedTerm::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_mission_limited_term_id' => $missionId,
            'status' => MissionStatus::UNCLEAR->value,
            'is_open' => MissionUnlockStatus::OPEN->value,
            'progress' => 1,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        $inGameBattleLog =[
            'partyStatus' => [
                [
                    'usrUnitId' => $usrUnitId,
                    'mstUnitId' => $mstUnit->getId(),
                    'color' => $mstUnit->getColor(),
                    'roleType' => $mstUnit->getRoleType(),
                    'hp' => 1,
                    'atk' => 1,
                    'moveSpeed' => '1.11',
                    'summonCost' => 1,
                    'summonCoolTime' => 1,
                    'damageKnockBackCount' => 1,
                    'specialAttackMstAttackId' => $specialAttackId,
                    'attackDelay' => 1,
                    'nextAttackInterval' => 1,
                    'mstUnitAbility1' => $mstUnit->getMstUnitAbility1(),
                    'mstUnitAbility2' => $mstUnit->getMstUnitAbility2(),
                    'mstUnitAbility3' => $mstUnit->getMstUnitAbility3(),
                ]
            ],
        ];

        // Exercise
        $this->useCase->exec($currentUser, $mstAdventBattleId, $partyNo, false, $inGameBattleLog);

        // Verify
        $usrMissions = UsrMissionLimitedTerm::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertEquals(1, $usrMissions->count());
        $this->checkUsrMissionLimitedTerm($usrMissions['challenge'], MissionStatus::CLEAR, 2, $now, $now, null);

        $usrUnits = UsrUnit::query()->where('usr_user_id', $usrUserId)->get();
        foreach ($usrUnits as $usrUnit) {
            $this->assertEquals(1, $usrUnit->getBattleCount());
        }
    }
}
