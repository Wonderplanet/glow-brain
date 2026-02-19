<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Enums\AdventBattleClearRewardCategory;
use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Enums\AdventBattleType;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\Models\UsrAdventBattleSessionInterface;
use App\Domain\AdventBattle\UseCases\AdventBattleEndUseCase;
use App\Domain\Cheat\Enums\CheatContentType;
use App\Domain\Cheat\Enums\CheatType;
use App\Domain\Cheat\Models\LogSuspectedUser;
use App\Domain\Cheat\Models\UsrCheatSession;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\InGame\Models\UsrEnemyDiscovery;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Entities\PartyStatus;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;
use App\Domain\Resource\Mst\Models\MstAdventBattle;
use App\Domain\Resource\Mst\Models\MstAdventBattleClearReward;
use App\Domain\Resource\Mst\Models\MstAttack;
use App\Domain\Resource\Mst\Models\MstCheatSetting;
use App\Domain\Resource\Mst\Models\MstEnemyCharacter;
use App\Domain\Resource\Mst\Models\MstMissionLimitedTerm;
use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Resource\Mst\Models\MstUnitGradeCoefficient;
use App\Domain\Resource\Mst\Models\MstUnitLevelUp;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Shop\Enums\PackType;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Enums\SaleCondition;
use App\Domain\Shop\Models\UsrConditionPack;
use App\Domain\Unit\Enums\AttackKind;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Constants\UserConstant;
use App\Domain\User\Models\UsrUserParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class AdventBattleEndUseCaseTest extends TestCase
{
    use TestMissionTrait;

    private AdventBattleEndUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(AdventBattleEndUseCase::class);
    }

    private function createMstTestData(): void
    {
        MstAdventBattle::factory()->createMany([
            [
                'id' => '10',
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
                'exp' => 1000,
                'coin' => 1000,
            ],
            [
                'id' => '11',
                'advent_battle_type' => AdventBattleType::RAID->value,
                'start_at' => '2024-03-01 00:00:00',
                'end_at' => '2024-06-01 00:00:00',
                'exp' => 1000,
                'coin' => 1000,
            ],
        ]);

        MstAdventBattleClearReward::factory()->createMany([
            [
                'mst_advent_battle_id' => '10',
                'reward_category' => AdventBattleClearRewardCategory::FIRST_CLEAR->value,
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_id' => '10',
                'reward_category' => AdventBattleClearRewardCategory::FIRST_CLEAR->value,
                'resource_type' => RewardType::EXP->value,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_id' => '10',
                'reward_category' => AdventBattleClearRewardCategory::ALWAYS->value,
                'resource_type' => RewardType::EXP->value,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_id' => '10',
                'reward_category' => AdventBattleClearRewardCategory::RANDOM->value,
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 200,
                'percentage' => 100,
            ],
            [
                'mst_advent_battle_id' => '10',
                'reward_category' => AdventBattleClearRewardCategory::RANDOM->value,
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
                'percentage' => 0,
            ],
            [
                'mst_advent_battle_id' => '11',
                'reward_category' => AdventBattleClearRewardCategory::FIRST_CLEAR->value,
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_id' => '11',
                'reward_category' => AdventBattleClearRewardCategory::FIRST_CLEAR->value,
                'resource_type' => RewardType::EXP->value,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_id' => '11',
                'reward_category' => AdventBattleClearRewardCategory::ALWAYS->value,
                'resource_type' => RewardType::EXP->value,
                'resource_amount' => 100,
            ],
            [
                'mst_advent_battle_id' => '11',
                'reward_category' => AdventBattleClearRewardCategory::RANDOM->value,
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 200,
                'percentage' => 100,
            ],
            [
                'mst_advent_battle_id' => '11',
                'reward_category' => AdventBattleClearRewardCategory::RANDOM->value,
                'resource_type' => RewardType::COIN->value,
                'resource_amount' => 100,
                'percentage' => 0,
            ],
        ]);

        MstCheatSetting::factory()->createMany([
            [
                'content_type' => CheatContentType::ADVENT_BATTLE->value,
                'cheat_type' => CheatType::BATTLE_TIME->value,
                'cheat_value' => 100,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
            ],
            [
                'content_type' => CheatContentType::ADVENT_BATTLE->value,
                'cheat_type' => CheatType::MAX_DAMAGE->value,
                'cheat_value' => 100000000,
                'is_excluded_ranking' => true,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
            ],
            [
                'content_type' => CheatContentType::ADVENT_BATTLE->value,
                'cheat_type' => CheatType::BATTLE_STATUS_MISMATCH->value,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
            ],
            [
                'content_type' => CheatContentType::ADVENT_BATTLE->value,
                'cheat_type' => CheatType::MASTER_DATA_STATUS_MISMATCH->value,
                'is_excluded_ranking' => true,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
            ],
        ]);

        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
            ['level' => 3, 'stamina' => 10, 'exp' => 1000],
        ]);
    }

    private function createMstTestDataLevelUp(): void
    {
        MstAdventBattle::factory()->createMany([
            [
                'id' => '10',
                'advent_battle_type' => AdventBattleType::SCORE_CHALLENGE->value,
                'start_at' => '2024-01-01 00:00:00',
                'end_at' => '2024-03-01 00:00:00',
                'exp' => 1000,
                'coin' => 1000,
            ],
            [
                'id' => '11',
                'advent_battle_type' => AdventBattleType::RAID->value,
                'start_at' => '2024-03-01 00:00:00',
                'end_at' => '2024-06-01 00:00:00',
                'exp' => 1000,
                'coin' => 1000,
            ],
        ]);

        MstUserLevel::factory()->createMany([
            ['level' => 1, 'stamina' => 10, 'exp' => 0],
            ['level' => 2, 'stamina' => 10, 'exp' => 100],
            ['level' => 3, 'stamina' => 10, 'exp' => 1000],
        ]);
    }

    #[DataProvider('params_exec_正常実行')]
    public function test_exec_正常実行(string $mstAdventBattleId, string $fixTime, int $allUserTotalScore, int $clearCount)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime($fixTime);
        $currentUser = new CurrentUser($usrUserId);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 5,
        ]);
        $this->createDiamond($usrUserId);
        $platform = UserConstant::PLATFORM_IOS;

        $score = 10000;
        $partyNo = 5;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => 1000,
            'total_score' => 1000,
            'clear_count' => $clearCount,
            'challenge_count' => 5,
            'reset_challenge_count' => 2,
            'reset_ad_challenge_count' => 3,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED->value,
            'party_no' => $partyNo,
            'battle_start_at' => $now->subSeconds(200)->toDateTimeString(),
        ]);
        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => CheatContentType::ADVENT_BATTLE->value,
            'target_id' => $mstAdventBattleId,
            'party_status' => json_encode([
                (new PartyStatus(
                    'usrUnit1',
                    'unit1',
                    'Red',
                    'Attack',
                    1,
                    1,
                    '1.11',
                    1,
                    1,
                    1,
                    '1001',
                    1,
                    1,
                    '2001',
                    '3001',
                    '4001',
                ))->formatToLog(),
            ]),
        ]);
        UsrEnemyDiscovery::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_enemy_character_id' => 'enemy1'],
        ]);

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
        // DropRユニットのグレード係数データを作成
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => 'DropR', 'grade_level' => 1, 'coefficient' => 0],
            ['unit_label' => 'DropR', 'grade_level' => 2, 'coefficient' => 3],
            ['unit_label' => 'DropR', 'grade_level' => 3, 'coefficient' => 5],
            ['unit_label' => 'DropR', 'grade_level' => 4, 'coefficient' => 8],
            ['unit_label' => 'DropR', 'grade_level' => 5, 'coefficient' => 10],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1'],
        ]);
        MstEnemyCharacter::factory()->createMany([
            ['id' => 'enemy1'],
            ['id' => 'enemy2'],
            ['id' => 'bossEnemy1'],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1'],
        ]);
        $inGameBattleLog = [
            'defeatEnemyCount' => 10,
            'defeatBossEnemyCount' => 1,
            'score' => $score,
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
            'maxDamage' => 99999999,
            'discoveredEnemies' => [
                ['mstEnemyCharacterId' => 'enemy1', 'count' => 3], // 発見済み
                ['mstEnemyCharacterId' => 'enemy2', 'count' => 5], // 新発見
                ['mstEnemyCharacterId' => 'bossEnemy1', 'count' => 1], // 新発見
                ['mstEnemyCharacterId' => 'invalidEnemy', 'count' => 999],
            ],
        ];

        // Exercise
        $response = $this->useCase->exec($currentUser, $mstAdventBattleId, $platform, $inGameBattleLog);

        // Verify
        $this->assertEquals($mstAdventBattleId, $response->usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($score, $response->usrAdventBattle->getMaxScore());
        $this->assertEquals(11000, $response->usrAdventBattle->getTotalScore());
        $this->assertEquals(2, $response->usrAdventBattle->getResetChallengeCount());
        $this->assertEquals(3, $response->usrAdventBattle->getResetAdChallengeCount());
        $this->assertEquals($allUserTotalScore, $response->allUserTotalScore);

        // 期待値定義
        $expectedAlwaysClearRewards = [
            'rewardCategory' => AdventBattleClearRewardCategory::ALWAYS->value,
            'reward' => [
                'resourceType' => RewardType::EXP->value,
                'resourceId' => '1',
                'resourceAmount' => 100,
                'preConversionResource' => null,
                'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
            ],
        ];
        $expectedRandomClearRewards = [
            'rewardCategory' => AdventBattleClearRewardCategory::RANDOM->value,
            'reward' => [
                'resourceType' => RewardType::COIN->value,
                'resourceId' => '1',
                'resourceAmount' => 200,
                'preConversionResource' => null,
                'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
            ],
        ];
        $expectedFirstClearRewards = [
            'rewardCategory' => AdventBattleClearRewardCategory::FIRST_CLEAR->value,
            'reward' => [
                'resourceType' => RewardType::COIN->value,
                'resourceId' => '1',
                'resourceAmount' => 100,
                'preConversionResource' => null,
                'unreceivedRewardReasonType' => UnreceivedRewardReason::NONE->value,
            ],
        ];

        $usrItems = $response->usrItems;

        $userLevelUpData = $response->userLevelUpData;
        $this->assertNotNull($userLevelUpData);
        $this->assertEquals(0, $userLevelUpData->beforeExp);
        $this->assertEquals(1000, $userLevelUpData->afterExp);

        $alwaysClearRewards = $response->adventBattleAlwaysClearRewards;
        $this->assertNotNull($alwaysClearRewards);
        $this->assertCount(1, $alwaysClearRewards);
        $this->assertEquals($expectedAlwaysClearRewards, $alwaysClearRewards->first()->formatToResponse());

        $randomClearRewards = $response->adventBattleRandomClearRewards;
        $this->assertNotNull($randomClearRewards);
        $this->assertCount(1, $randomClearRewards);
        $this->assertEquals($expectedRandomClearRewards, $randomClearRewards->first()->formatToResponse());

        $firstClearRewards = $response->adventBattleFirstClearRewards;
        if ($clearCount === 0) {
            $this->assertNotNull($firstClearRewards);
            $this->assertCount(2, $firstClearRewards);
            $this->assertEquals($expectedFirstClearRewards, $firstClearRewards->first()->formatToResponse());
        } else {
            $this->assertNotNull($firstClearRewards);
            $this->assertCount(0, $firstClearRewards);
        }

        $dropRewards = $response->adventBattleDropRewards;
        $this->assertNotNull($dropRewards);
        $this->assertCount(2, $dropRewards);

        $this->assertCount(2, $response->newUsrEnemyDiscoveries);
        $this->assertEqualsCanonicalizing(
            ['enemy2', 'bossEnemy1'],
            $response->newUsrEnemyDiscoveries->map->getMstEnemyCharacterId()->toArray(),
        );

        // DB確認
        $expectedCoin = 5 + 1000 + 200 + ($clearCount === 0 ? 100 : 0);
        $usrUserParameter->refresh();
        $this->assertEquals(3, $usrUserParameter->getLevel());
        $this->assertEquals(1000, $usrUserParameter->getExp());
        $this->assertEquals($expectedCoin, $usrUserParameter->getCoin());

        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUserId)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($usrUserId, $usrAdventBattle->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        $this->assertEquals(11000, $response->usrAdventBattle->getTotalScore());
        $this->assertEquals(5, $response->usrAdventBattle->getChallengeCount());
        $this->assertEquals(2, $response->usrAdventBattle->getResetChallengeCount());
        $this->assertEquals(3, $response->usrAdventBattle->getResetAdChallengeCount());
        $this->assertFalse($usrAdventBattle->isExcludedRanking());

        /** @var UsrAdventBattleSessionInterface $usrAdventBattleSession */
        $usrAdventBattleSession = UsrAdventBattleSession::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrUserId, $usrAdventBattleSession->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattleSession->getMstAdventBattleId());
        $this->assertEquals(AdventBattleSessionStatus::CLOSED, $usrAdventBattleSession->getIsValid());
        $this->assertEquals($partyNo, $usrAdventBattleSession->getPartyNo());

        $logSuspectedUser = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertNull($logSuspectedUser);

        // 発見した敵情報の確認
        $this->assertEqualsCanonicalizing(
            ['enemy1', 'enemy2', 'bossEnemy1'],
            UsrEnemyDiscovery::query()->where('usr_user_id', $usrUserId)->get()->map->getMstEnemyCharacterId()->toArray()
        );
    }

    public static function params_exec_正常実行()
    {
        return [
            'スコアチャレンジ' => ['10', '2024-01-10 00:00:00', 0, 0],
            '協力バトル' => ['11', '2024-03-10 00:00:00', 10000, 0],
            'スコアチャレンジ2回目クリア' => ['10', '2024-01-10 00:00:00', 0, 1],
            '協力バトル2回目クリア' => ['11', '2024-03-10 00:00:00', 10000, 1],
        ];
    }

    /**
     * @dataProvider params_exec_正常実行ユーザーレベルアップ
     */
    public function test_exec_正常実行ユーザーレベルアップ(string $mstAdventBattleId, string $fixTime, int $allUserTotalScore)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime($fixTime);
        $oprProduct = OprProduct::factory()->create(['product_type' => ProductType::PACK->value])->toEntity();
        $mstPackId = MstPack::factory()->create([
            'product_sub_id' => $oprProduct->getId(),
            'sale_condition' => SaleCondition::USER_LEVEL->value,
            'pack_type'=> PackType::NORMAL->value,
            'sale_condition_value' => '2',
        ])->toEntity()->getId();
        $currentUser = new CurrentUser($usrUserId);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 5,
        ]);
        $this->createDiamond($usrUserId);
        $platform = UserConstant::PLATFORM_IOS;

        $score = 10000;
        $inGameBattleLog = [
            'defeatEnemyCount' => 10,
            'defeatBossEnemyCount' => 1,
            'score' => $score,
        ];
        $partyNo = 5;
        $this->createMstTestDataLevelUp();
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => 1000,
            'total_score' => 1000,
            'challenge_count' => 5,
            'reset_challenge_count' => 2,
            'reset_ad_challenge_count' => 3,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED->value,
            'party_no' => $partyNo,
        ]);

        // Exercise
        $response = $this->useCase->exec($currentUser, $mstAdventBattleId, $platform, $inGameBattleLog);

        // Verify
        $this->assertEquals($mstAdventBattleId, $response->usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($score, $response->usrAdventBattle->getMaxScore());
        $this->assertEquals(11000, $response->usrAdventBattle->getTotalScore());
        $this->assertEquals(2, $response->usrAdventBattle->getResetChallengeCount());
        $this->assertEquals(3, $response->usrAdventBattle->getResetAdChallengeCount());
        $this->assertEquals($allUserTotalScore, $response->allUserTotalScore);

        $userLevelUpData = $response->userLevelUpData;
        $this->assertNotNull($userLevelUpData);
        $this->assertEquals(0, $userLevelUpData->beforeExp);
        $this->assertEquals(1000, $userLevelUpData->afterExp);

        $dropRewards = $response->adventBattleDropRewards;
        $this->assertNotNull($dropRewards);
        $this->assertCount(2, $dropRewards);

        $usrConditionPack = $response->usrConditionPacks->first();
        $this->assertNotNull($usrConditionPack);
        $this->assertEquals($mstPackId, $usrConditionPack->getMstPackId());

        // DB確認
        $usrUserParameter->refresh();
        $this->assertEquals(3, $usrUserParameter->getLevel());
        $this->assertEquals(1000, $usrUserParameter->getExp());
        $this->assertEquals(5 + 1000, $usrUserParameter->getCoin());

        $usrConditionPack = UsrConditionPack::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_pack_id', $mstPackId)
            ->first();
        $this->assertNotNull($usrConditionPack);

        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUserId)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($usrUserId, $usrAdventBattle->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        $this->assertEquals(11000, $response->usrAdventBattle->getTotalScore());
        $this->assertEquals(5, $response->usrAdventBattle->getChallengeCount());
        $this->assertEquals(2, $response->usrAdventBattle->getResetChallengeCount());
        $this->assertEquals(3, $response->usrAdventBattle->getResetAdChallengeCount());
        $this->assertFalse($usrAdventBattle->isExcludedRanking());

        /** @var UsrAdventBattleSessionInterface $usrAdventBattleSession */
        $usrAdventBattleSession = UsrAdventBattleSession::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrUserId, $usrAdventBattleSession->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattleSession->getMstAdventBattleId());
        $this->assertEquals(AdventBattleSessionStatus::CLOSED, $usrAdventBattleSession->getIsValid());
        $this->assertEquals($partyNo, $usrAdventBattleSession->getPartyNo());
    }

    public static function params_exec_正常実行ユーザーレベルアップ()
    {
        return [
            'スコアチャレンジ(ユーザーレベルアップ)' => ['10', '2024-01-10 00:00:00', 0],
            '協力バトル(ユーザーレベルアップ)' => ['11', '2024-03-10 00:00:00', 10000],
        ];
    }

    public function test_exec_開催期間外()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime("2024-06-01 00:00:01");
        $currentUser = new CurrentUser($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
        ]);
        $this->createDiamond($usrUserId);
        $platform = UserConstant::PLATFORM_IOS;

        $score = 10000;
        $mstAdventBattleId = '11';
        $this->createMstTestData();
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);

        $inGameBattleLog = [
            'defeatEnemyCount' => 10,
            'defeatBossEnemyCount' => 1,
            'score' => $score,
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
            'maxDamage' => 99999999,
            'discoveredEnemies' => [
            ],
        ];

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ADVENT_BATTLE_PERIOD_OUTSIDE);
        $this->useCase->exec($currentUser, $mstAdventBattleId, $platform, $inGameBattleLog);
    }

    public function test_exec_セッション無し()
    {
        // Setup
        $mstAdventBattleId = '10';
        $usrUserId = $this->createUsrUser()->getId();
        $this->fixTime('2024-01-10 00:00:00');
        $currentUser = new CurrentUser($usrUserId);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 5,
        ]);
        $this->createDiamond($usrUserId);
        $platform = UserConstant::PLATFORM_IOS;
        $inGameBattleLog = [
            'defeatEnemyCount' => 10,
            'defeatBossEnemyCount' => 1,
            'score' => 10000,
        ];
        $this->createMstTestData();

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ADVENT_BATTLE_SESSION_MISMATCH);
        $this->expectExceptionMessage('usr_advent_battle_session is not found.');
        $this->useCase->exec($currentUser, $mstAdventBattleId, $platform, $inGameBattleLog);
    }

    #[DataProvider('params_exec_チート検出')]
    public function test_exec_チート検出(CheatType $cheatType,  $battleTime, int $maxDamage, int $hp, string $mstUnitId, bool $isExcludedRanking)
    {
        // Setup
        $platform = UserConstant::PLATFORM_IOS;
        $mstAdventBattleId = '10';
        $fixTime = '2024-01-10 00:00:00';

        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime($fixTime);
        $currentUser = new CurrentUser($usrUserId);

        $score = 10000;
        $partyNo = 5;
        $unitLabel = 'DropR';
        $this->createMstTestData();
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => 1000,
            'total_score' => 1000,
            'challenge_count' => 5,
            'reset_challenge_count' => 2,
            'reset_ad_challenge_count' => 3,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED->value,
            'party_no' => $partyNo,
            'battle_start_at' => $now->subSeconds($battleTime)->toDateTimeString(),
        ]);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'level' => 1,
            'exp' => 0,
            'coin' => 5,
        ]);
        $this->createDiamond($usrUserId);

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
            ],
            [
                'id' => 'unit2',
                'color' => 'Red',
                'mst_unit_ability_id1' => '2001',
                'ability_unlock_rank1' => 1,
                'mst_unit_ability_id2' => '3001',
                'ability_unlock_rank2' => 1,
                'mst_unit_ability_id3' => '4001',
                'ability_unlock_rank3' => 1,
                'move_speed' => 2
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
            ['id' => '1001', 'mst_unit_id' => $mstUnitId, 'attack_kind' => AttackKind::SPECIAL->value],
        ]);
        // DropRユニットのグレード係数データを作成
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => 'DropR', 'grade_level' => 1, 'coefficient' => 0],
            ['unit_label' => 'DropR', 'grade_level' => 2, 'coefficient' => 3],
            ['unit_label' => 'DropR', 'grade_level' => 3, 'coefficient' => 5],
            ['unit_label' => 'DropR', 'grade_level' => 4, 'coefficient' => 8],
            ['unit_label' => 'DropR', 'grade_level' => 5, 'coefficient' => 10],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => $mstUnitId],
        ]);
        UsrParty::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1'],
        ]);
        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => CheatContentType::ADVENT_BATTLE->value,
            'target_id' => $mstAdventBattleId,
            'party_status' => json_encode([
                (new PartyStatus(
                    'usrUnit1',
                    $mstUnitId,
                    'Red',
                    'Attack',
                    $hp,
                    1,
                    '1.11',
                    1,
                    1,
                    1,
                    '1001',
                    1,
                    1,
                    '2001',
                    '3001',
                    '4001',
                ))->formatToLog(),
            ]),
        ]);
        $inGameBattleLog = [
            'defeatEnemyCount' => 10,
            'defeatBossEnemyCount' => 1,
            'score' => $score,
            'partyStatus' => [
                [
                    'usrUnitId' => 'usrUnit1',
                    'mstUnitId' => $mstUnitId,
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
            'maxDamage' => $maxDamage,
        ];

        // Exercise
        $response = $this->useCase->exec($currentUser, $mstAdventBattleId, $platform, $inGameBattleLog);

        // Verify
        $this->assertEquals($mstAdventBattleId, $response->usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($score, $response->usrAdventBattle->getMaxScore());
        $this->assertEquals(11000, $response->usrAdventBattle->getTotalScore());
        $this->assertEquals(2, $response->usrAdventBattle->getResetChallengeCount());
        $this->assertEquals(3, $response->usrAdventBattle->getResetAdChallengeCount());

        /** @var UsrAdventBattleInterface $usrAdventBattle */
        $usrAdventBattle = UsrAdventBattle::where('usr_user_id', $usrUserId)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($usrUserId, $usrAdventBattle->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattle->getMstAdventBattleId());
        $this->assertEquals($now->toDateTimeString(), $usrAdventBattle->getLatestResetAt());
        $this->assertEquals(11000, $response->usrAdventBattle->getTotalScore());
        $this->assertEquals(5, $response->usrAdventBattle->getChallengeCount());
        $this->assertEquals(2, $response->usrAdventBattle->getResetChallengeCount());
        $this->assertEquals(3, $response->usrAdventBattle->getResetAdChallengeCount());
        if ($isExcludedRanking) {
            $this->assertTrue($usrAdventBattle->isExcludedRanking());
        } else {
            $this->assertFalse($usrAdventBattle->isExcludedRanking());
        }

        /** @var UsrAdventBattleSessionInterface $usrAdventBattleSession */
        $usrAdventBattleSession = UsrAdventBattleSession::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrUserId, $usrAdventBattleSession->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattleSession->getMstAdventBattleId());
        $this->assertEquals(AdventBattleSessionStatus::CLOSED, $usrAdventBattleSession->getIsValid());
        $this->assertEquals($partyNo, $usrAdventBattleSession->getPartyNo());

        /** @var LogSuspectedUser $logSuspectedUser */
        $logSuspectedUsers = LogSuspectedUser::query()->where('usr_user_id', $usrUserId)->get();
        $this->assertEquals(1, $logSuspectedUsers->count());
        $logSuspectedUser = $logSuspectedUsers->get(0);
        $this->assertEquals(CheatContentType::ADVENT_BATTLE->value, $logSuspectedUser->content_type);
        $this->assertEquals($cheatType->value, $logSuspectedUser->cheat_type);
        $this->assertEquals($mstAdventBattleId, $logSuspectedUser->target_id);
        $this->assertNotEmpty($logSuspectedUser->detail);
    }

    public static function params_exec_チート検出()
    {
        return [
            'バトル時間チート' => [CheatType::BATTLE_TIME, 10, 99999999, 1, 'unit1', false],
            '最大ダメージ値チート' => [CheatType::MAX_DAMAGE, 200, 100000000, 1, 'unit1', true],
            'バトル前後のステータス不一致チート' => [CheatType::BATTLE_STATUS_MISMATCH, 200, 99999999, 10, 'unit1', false],
            // 'マスターデータとのステータス不一致チート' => [CheatType::MASTER_DATA_STATUS_MISMATCH, 200, 99999999, 1, 'unit2', true], // MasterDataStatusMismatchチェックが無効化されているため無効化
        ];
    }

    public function test_exec_ミッション進捗確認()
    {
        // 実装が漏れていたので確認するためのテストを追加
        // Setup
        $mstAdventBattleId = '10';
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime('2024-01-01 00:00:00');
        $currentUser = new CurrentUser($usrUserId);
        UsrUserParameter::factory()->create(['usr_user_id' => $usrUserId]);
        $this->createDiamond($usrUserId);
        $platform = UserConstant::PLATFORM_IOS;

        $score = 10000;
        $partyNo = 5;
        $unitLabel = 'DropR';
        $this->createMstTestData();

        $mstUnit = MstUnit::factory()->create([
            'id' => 'unit1',
            'color' => 'Red',
            'mst_unit_ability_id1' => '2001',
            'mst_unit_ability_id2' => '3001',
            'mst_unit_ability_id3' => '4001',
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
        $specialAttackId = MstAttack::factory()->create([
            'id' => '1001',
            'mst_unit_id' => $mstUnit->getId(),
            'attack_kind' => AttackKind::SPECIAL->value
        ])->toEntity()->getId();
        // DropRユニットのグレード係数データを作成
        MstUnitGradeCoefficient::factory()->createMany([
            ['unit_label' => 'DropR', 'grade_level' => 1, 'coefficient' => 0],
            ['unit_label' => 'DropR', 'grade_level' => 2, 'coefficient' => 3],
            ['unit_label' => 'DropR', 'grade_level' => 3, 'coefficient' => 5],
            ['unit_label' => 'DropR', 'grade_level' => 4, 'coefficient' => 8],
            ['unit_label' => 'DropR', 'grade_level' => 5, 'coefficient' => 10],
        ]);
        MstMissionLimitedTerm::factory()->createMany([
            // 開催中の期間限定ミッション
            [
                'id' => 'score',
                'progress_group_key' => 'progress_1',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_SCORE->value,
                'criterion_value' => null,
                'criterion_count' => 20000,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
            ],
            // 未開催の期間限定ミッション
            [
                'id' => 'total_score',
                'progress_group_key' => 'progress_2',
                'criterion_type' => MissionCriterionType::ADVENT_BATTLE_TOTAL_SCORE->value,
                'criterion_value' => null,
                'criterion_count' => 20000,
                'mission_category' => MissionLimitedTermCategory::ADVENT_BATTLE->value,
            ],
        ]);
        $usrUnitId = UsrUnit::factory()->create(['id' => 'usrUnit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => $mstUnit->getId()])->getId();
        MstEnemyCharacter::factory()->createMany([
            ['id' => 'enemy1'],
            ['id' => 'enemy2'],
            ['id' => 'bossEnemy1'],
        ]);
        UsrMissionLimitedTerm::factory()->createMany([
            ['usr_user_id' => $usrUserId, 'mst_mission_limited_term_id' => 'score', 'status' => MissionStatus::UNCLEAR->value, 'progress' => 15000, 'is_open' => MissionUnlockStatus::OPEN->value, 'latest_reset_at' => $now],
            ['usr_user_id' => $usrUserId, 'mst_mission_limited_term_id' => 'total_score', 'status' => MissionStatus::UNCLEAR->value, 'progress' => 15000, 'is_open' => MissionUnlockStatus::OPEN->value, 'latest_reset_at' => $now],
        ]);
        UsrParty::factory()->create(['usr_user_id' => $usrUserId, 'party_no' => $partyNo, 'usr_unit_id_1' => 'usrUnit1']);
        UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => 15000,
            'total_score' => 15000,
            'latest_reset_at' => $now->toDateTimeString(),
        ]);
        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED->value,
            'party_no' => $partyNo,
            'battle_start_at' => $now->subSeconds(200)->toDateTimeString(),
        ]);
        UsrCheatSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'content_type' => CheatContentType::ADVENT_BATTLE->value,
            'target_id' => $mstAdventBattleId,
            'party_status' => json_encode([
                (new PartyStatus(
                    $usrUnitId,
                    $mstUnit->getId(),
                    $mstUnit->getColor(),
                    $mstUnit->getRoleType(),
                    1,
                    1,
                    '1.11',
                    1,
                    1,
                    1,
                    $specialAttackId,
                    1,
                    1,
                    $mstUnit->getMstUnitAbility1(),
                    $mstUnit->getMstUnitAbility2(),
                    $mstUnit->getMstUnitAbility3(),
                ))->formatToLog(),
            ]),
        ]);
        $inGameBattleLog = [
            'defeatEnemyCount' => 10,
            'defeatBossEnemyCount' => 1,
            'score' => $score,
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
            'maxDamage' => 99999999,
            'discoveredEnemies' => [
                ['mstEnemyCharacterId' => 'enemy1', 'count' => 3],
                ['mstEnemyCharacterId' => 'enemy2', 'count' => 5],
                ['mstEnemyCharacterId' => 'bossEnemy1', 'count' => 1],
                ['mstEnemyCharacterId' => 'invalidEnemy', 'count' => 999],
            ],
        ];

        // Exercise
        $this->useCase->exec($currentUser, $mstAdventBattleId, $platform, $inGameBattleLog);

        // Verify
        $usrMissions = UsrMissionLimitedTerm::query()->where('usr_user_id', $usrUserId)->get()->keyBy->getMstMissionId();
        $this->assertCount(2, $usrMissions);
        $this->checkUsrMissionLimitedTerm($usrMissions['score'], MissionStatus::UNCLEAR, 15000, $now, null, null);
        $this->checkUsrMissionLimitedTerm($usrMissions['total_score'], MissionStatus::CLEAR, 20000, $now, $now, null);
    }
}
