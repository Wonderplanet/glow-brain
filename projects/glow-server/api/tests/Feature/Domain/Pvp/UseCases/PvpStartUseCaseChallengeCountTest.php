<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp\UseCases;

use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Models\UsrItem;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\UseCases\PvpStartUseCase;
use App\Domain\Resource\Mst\Models\MstConfig;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserProfile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PvpStartUseCaseChallengeCountTest extends TestCase
{
    private PvpStartUseCase $pvpStartUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pvpStartUseCase = app(PvpStartUseCase::class);
    }

    #[DataProvider('challengeCountValidationProvider')]
    public function testExec_挑戦回数バリデーション(
        int $remainingChallengeCount,
        int $remainingItemChallengeCount,
        int $isUseItem,
        bool $shouldThrowException,
        string $expectedMessage = ''
    ): void {
        // Arrange
        $usrUserId = 'user_challenge_test';
        $this->setUsrUserId($usrUserId);
        
        $user = new CurrentUser($usrUserId);
        $sysPvpSeasonId = '1';
        $myId = 'my_id_123';
        $partyNo = 1;
        $inGameBattleLog = [];

        // マスターデータを準備
        $this->setupMasterData();
        $this->setupUserData($usrUserId);

        // PVPシーズンを作成
        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        // MstPvpを作成
        MstPvp::factory()->create([
            'id' => 'default_pvp',
            'item_challenge_cost_amount' => 1, // アイテム挑戦時のコスト
        ]);

        // テスト用の対戦相手データを作成（JSON文字列で設定）
        $selectedOpponentCandidatesArray = [
            $myId => [
                'pvpUserProfile' => [
                    'myId' => $myId,
                    'name' => 'テスト対戦相手',
                    'mstUnitId' => 'test_unit_001',
                    'mstEmblemId' => 'emblem_001',
                    'score' => 1200,
                    'mstUnitIds' => ['test_unit_001', 'test_unit_002'],
                    'winAddPoint' => 50,
                ],
                'unitStatuses' => [
                    [
                        'mstUnitId' => 'test_unit_001',
                        'level' => 60,
                        'rank' => 6,
                        'gradeLevel' => 3,
                    ],
                    [
                        'mstUnitId' => 'test_unit_002',
                        'level' => 55,
                        'rank' => 5,
                        'gradeLevel' => 2,
                    ],
                ],
                'usrOutpostEnhancements' => [],
                'usrEncyclopediaEffects' => [],
                'mstArtworkIds' => [],
            ]
        ];
        $selectedOpponentCandidates = $selectedOpponentCandidatesArray;

        // UsrPvpを作成（挑戦回数を指定）
        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'daily_remaining_challenge_count' => $remainingChallengeCount,
            'daily_remaining_item_challenge_count' => $remainingItemChallengeCount,
            'score' => 1000,
            'selected_opponent_candidates' => $selectedOpponentCandidates,
        ]);

        // アイテム使用時にはユーザーアイテムを作成
        if ($isUseItem === 1) {
            \App\Domain\Item\Models\Eloquent\UsrItem::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_item_id' => 'pvp_challenge_item',
                'amount' => 10, // 十分な数を持たせる
            ]);
        }

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // Act & Assert
        if ($shouldThrowException) {
            $this->expectException(GameException::class);
            if ($expectedMessage) {
                $this->expectExceptionMessage($expectedMessage);
            }
        }

        $result = $this->pvpStartUseCase->exec(
            $user,
            $sysPvpSeasonId,
            (bool)$isUseItem,
            $myId,
            $partyNo,
            $inGameBattleLog
        );

        if (!$shouldThrowException) {
            // 正常ケースの検証
            $this->assertNotNull($result);
            $this->assertNotNull($result->getOpponentPvpStatus());

            // PVPセッションがDBに保存されていることを確認
            $usrPvpSession = UsrPvpSession::query()
                ->where('usr_user_id', $usrUserId)
                ->first();

            $this->assertNotNull($usrPvpSession);
            $this->assertEquals($usrUserId, $usrPvpSession->getUsrUserId());
            $this->assertEquals($sysPvpSeasonId, $usrPvpSession->getSysPvpSeasonId());
            $this->assertEquals($partyNo, $usrPvpSession->getPartyNo());
        }
    }

    public static function challengeCountValidationProvider(): array
    {
        return [
            'デイリー挑戦回数使用_挑戦可能' => [
                'remainingChallengeCount' => 5,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 0,
                'shouldThrowException' => false,
            ],
            'デイリー挑戦回数使用_挑戦不可_回数0' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'isUseItem' => 0,
                'shouldThrowException' => true,
                'expectedMessage' => 'daily challenge count is over',
            ],
            'デイリー挑戦回数使用_挑戦可能_回数1' => [
                'remainingChallengeCount' => 1,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 0,
                'shouldThrowException' => false,
            ],
            'アイテム挑戦回数使用_挑戦可能' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'isUseItem' => 1,
                'shouldThrowException' => false,
            ],
            'アイテム挑戦回数使用_挑戦不可_回数0' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 1,
                'shouldThrowException' => true,
                'expectedMessage' => 'item challenge count is over',
            ],
            'アイテム挑戦回数使用_挑戦不可_デイリー回数残り' => [
                'remainingChallengeCount' => 2,
                'remainingItemChallengeCount' => 5,
                'isUseItem' => 1,
                'shouldThrowException' => true,
                'expectedMessage' => 'daily challenge count must be 0 to use item challenge count',
            ],
            'アイテム挑戦回数使用_挑戦可能_回数1' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 1,
                'isUseItem' => 1,
                'shouldThrowException' => false,
            ],
        ];
    }

    #[DataProvider('itemAmountValidationProvider')]
    public function testExec_アイテム所持数バリデーション(
        int $remainingChallengeCount,
        int $remainingItemChallengeCount,
        int $itemAmount,
        int $isUseItem,
        bool $shouldThrowException,
        string $expectedMessage = ''
    ): void {
        // Arrange
        $usrUserId = 'user_item_test';
        $this->setUsrUserId($usrUserId);
        
        $user = new CurrentUser($usrUserId);
        $sysPvpSeasonId = '1';
        $myId = 'my_id_123';
        $partyNo = 1;
        $inGameBattleLog = [];

        // マスターデータを準備
        $this->setupMasterData();
        $this->setupUserData($usrUserId);

        // PVPシーズンを作成
        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        // MstPvpを作成（アイテム所持数バリデーション用にコスト2を設定）
        MstPvp::factory()->create([
            'id' => 'default_pvp',
            'item_challenge_cost_amount' => 2, // アイテム挑戦時のコスト
        ]);

        // テスト用の対戦相手データを作成（JSON文字列で設定）
        $selectedOpponentCandidatesArray = [
            $myId => [
                'pvpUserProfile' => [
                    'myId' => $myId,
                    'name' => 'テスト対戦相手',
                    'mstUnitId' => 'test_unit_001',
                    'mstEmblemId' => 'emblem_001',
                    'score' => 1200,
                    'mstUnitIds' => ['test_unit_001', 'test_unit_002'],
                    'winAddPoint' => 50,
                ],
                'unitStatuses' => [
                    [
                        'mstUnitId' => 'test_unit_001',
                        'level' => 60,
                        'rank' => 6,
                        'gradeLevel' => 3,
                    ],
                    [
                        'mstUnitId' => 'test_unit_002',
                        'level' => 55,
                        'rank' => 5,
                        'gradeLevel' => 2,
                    ],
                ],
                'usrOutpostEnhancements' => [],
                'usrEncyclopediaEffects' => [],
                'mstArtworkIds' => [],
            ]
        ];
        $selectedOpponentCandidates = $selectedOpponentCandidatesArray;

        // UsrPvpを作成（挑戦回数を指定）
        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'daily_remaining_challenge_count' => $remainingChallengeCount,
            'daily_remaining_item_challenge_count' => $remainingItemChallengeCount,
            'score' => 1000,
            'selected_opponent_candidates' => $selectedOpponentCandidates,
        ]);

        // ユーザーアイテムを作成（指定された数量）
        if ($itemAmount > 0) {
            \App\Domain\Item\Models\Eloquent\UsrItem::factory()->create([
                'usr_user_id' => $usrUserId,
                'mst_item_id' => 'pvp_challenge_item',
                'amount' => $itemAmount,
            ]);
        }

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // Act & Assert
        if ($shouldThrowException) {
            $this->expectException(GameException::class);
            if ($expectedMessage) {
                $this->expectExceptionMessage($expectedMessage);
            }
        }

        $result = $this->pvpStartUseCase->exec(
            $user,
            $sysPvpSeasonId,
            (bool)$isUseItem,
            $myId,
            $partyNo,
            $inGameBattleLog
        );

        if (!$shouldThrowException) {
            // 正常ケースの検証
            $this->assertNotNull($result);
            $this->assertNotNull($result->getOpponentPvpStatus());

            // PVPセッションがDBに保存されていることを確認
            $usrPvpSession = UsrPvpSession::query()
                ->where('usr_user_id', $usrUserId)
                ->first();

            $this->assertNotNull($usrPvpSession);
            $this->assertEquals($usrUserId, $usrPvpSession->getUsrUserId());
            $this->assertEquals($sysPvpSeasonId, $usrPvpSession->getSysPvpSeasonId());
            $this->assertEquals($partyNo, $usrPvpSession->getPartyNo());
        }
    }

    public static function itemAmountValidationProvider(): array
    {
        return [
            'アイテム使用_所持数十分_挑戦可能' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'itemAmount' => 10,
                'isUseItem' => 1,
                'shouldThrowException' => false,
            ],
            'アイテム使用_所持数不足_挑戦不可' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'itemAmount' => 1, // アイテム所持数が不足（コスト2に対して1個）
                'isUseItem' => 1,
                'shouldThrowException' => true,
                'expectedMessage' => 'not enough item for PVP challenge',
            ],
            'アイテム使用_アイテム未所持_挑戦不可' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 3,
                'itemAmount' => 0, // アイテム未所持
                'isUseItem' => 1,
                'shouldThrowException' => true,
                'expectedMessage' => 'not enough item for PVP challenge',
            ],
            'アイテム使用_所持数ちょうど_挑戦可能' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 1,
                'itemAmount' => 2, // コストと同じ数
                'isUseItem' => 1,
                'shouldThrowException' => false,
            ],
            'アイテム使用_所持数コスト+1_挑戦可能' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 2,
                'itemAmount' => 3, // コスト+1
                'isUseItem' => 1,
                'shouldThrowException' => false,
            ],
        ];
    }

    #[DataProvider('itemNotUsedValidationProvider')]
    public function testExec_アイテム未使用時バリデーション(
        int $remainingChallengeCount,
        int $remainingItemChallengeCount,
        int $isUseItem,
        bool $shouldThrowException,
        string $expectedMessage = ''
    ): void {
        // Arrange
        $usrUserId = 'user_item_not_used_test';
        $this->setUsrUserId($usrUserId);
        
        $user = new CurrentUser($usrUserId);
        $sysPvpSeasonId = '1';
        $myId = 'my_id_123';
        $partyNo = 1;
        $inGameBattleLog = [];

        // マスターデータを準備
        $this->setupMasterData();
        $this->setupUserData($usrUserId);

        // PVPシーズンを作成
        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        // MstPvpを作成
        MstPvp::factory()->create([
            'id' => 'default_pvp',
            'item_challenge_cost_amount' => 1,
        ]);

        // テスト用の対戦相手データを作成（JSON文字列で設定）
        $selectedOpponentCandidatesArray = [
            $myId => [
                'pvpUserProfile' => [
                    'myId' => $myId,
                    'name' => 'テスト対戦相手',
                    'mstUnitId' => 'test_unit_001',
                    'mstEmblemId' => 'emblem_001',
                    'score' => 1200,
                    'mstUnitIds' => ['test_unit_001', 'test_unit_002'],
                    'winAddPoint' => 50,
                ],
                'unitStatuses' => [
                    [
                        'mstUnitId' => 'test_unit_001',
                        'level' => 60,
                        'rank' => 6,
                        'gradeLevel' => 3,
                    ],
                    [
                        'mstUnitId' => 'test_unit_002',
                        'level' => 55,
                        'rank' => 5,
                        'gradeLevel' => 2,
                    ],
                ],
                'usrOutpostEnhancements' => [],
                'usrEncyclopediaEffects' => [],
                'mstArtworkIds' => [],
            ]
        ];
        $selectedOpponentCandidates = $selectedOpponentCandidatesArray;

        // UsrPvpを作成
        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'daily_remaining_challenge_count' => $remainingChallengeCount,
            'daily_remaining_item_challenge_count' => $remainingItemChallengeCount,
            'score' => 1000,
            'selected_opponent_candidates' => $selectedOpponentCandidates,
        ]);

        // アイテム未使用時でもアイテムデータは作成（使用しないだけ）
        \App\Domain\Item\Models\Eloquent\UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'pvp_challenge_item',
            'amount' => 5,
        ]);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // Act & Assert
        if ($shouldThrowException) {
            $this->expectException(GameException::class);
            if ($expectedMessage) {
                $this->expectExceptionMessage($expectedMessage);
            }
        }

        $result = $this->pvpStartUseCase->exec(
            $user,
            $sysPvpSeasonId,
            (bool)$isUseItem,
            $myId,
            $partyNo,
            $inGameBattleLog
        );

        if (!$shouldThrowException) {
            // 正常ケースの検証
            $this->assertNotNull($result);
            $this->assertNotNull($result->getOpponentPvpStatus());
        }
    }

    public static function itemNotUsedValidationProvider(): array
    {
        return [
            'アイテム未使用_デイリー挑戦回数あり_挑戦可能' => [
                'remainingChallengeCount' => 3,
                'remainingItemChallengeCount' => 2,
                'isUseItem' => 0,
                'shouldThrowException' => false,
            ],
            'アイテム未使用_デイリー挑戦回数なし_挑戦不可' => [
                'remainingChallengeCount' => 0,
                'remainingItemChallengeCount' => 5,
                'isUseItem' => 0,
                'shouldThrowException' => true,
                'expectedMessage' => 'daily challenge count is over',
            ],
            'アイテム未使用_デイリー挑戦回数1_挑戦可能' => [
                'remainingChallengeCount' => 1,
                'remainingItemChallengeCount' => 0,
                'isUseItem' => 0,
                'shouldThrowException' => false,
            ],
        ];
    }

    public function testExec_挑戦回数は消費されないことを確認(): void
    {
        // Arrange
        $usrUserId = 'user_no_consume';
        $this->setUsrUserId($usrUserId);
        
        $user = new CurrentUser($usrUserId);
        $sysPvpSeasonId = '1';
        $myId = 'my_id_123';
        $partyNo = 1;
        $inGameBattleLog = [];
        $isUseItem = 0;

        $this->setupMasterData();
        $this->setupUserData($usrUserId);

        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        MstPvp::factory()->create([
            'id' => 'default_pvp',
            'item_challenge_cost_amount' => 1, // アイテム挑戦時のコスト
        ]);

        $initialChallengeCount = 5;
        $initialItemChallengeCount = 3;

        // テスト用の対戦相手データを作成（JSON文字列で設定）
        $selectedOpponentCandidatesArray = [
            $myId => [
                'pvpUserProfile' => [
                    'myId' => $myId,
                    'name' => 'テスト対戦相手',
                    'mstUnitId' => 'test_unit_001',
                    'mstEmblemId' => 'emblem_001',
                    'score' => 1200,
                    'mstUnitIds' => ['test_unit_001', 'test_unit_002'],
                    'winAddPoint' => 50,
                ],
                'unitStatuses' => [
                    [
                        'mstUnitId' => 'test_unit_001',
                        'level' => 60,
                        'rank' => 6,
                        'gradeLevel' => 3,
                    ],
                    [
                        'mstUnitId' => 'test_unit_002',
                        'level' => 55,
                        'rank' => 5,
                        'gradeLevel' => 2,
                    ],
                ],
                'usrOutpostEnhancements' => [],
                'usrEncyclopediaEffects' => [],
                'mstArtworkIds' => [],
            ]
        ];
        $selectedOpponentCandidates = $selectedOpponentCandidatesArray;

        UsrPvp::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => (string)$sysPvpSeasonId,
            'daily_remaining_challenge_count' => $initialChallengeCount,
            'daily_remaining_item_challenge_count' => $initialItemChallengeCount,
            'score' => 1000,
            'selected_opponent_candidates' => $selectedOpponentCandidates,
        ]);

        // アイテム使用時でないが、設定は作成しておく
        \App\Domain\Item\Models\Eloquent\UsrItem::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_item_id' => 'pvp_challenge_item',
            'amount' => 10,
        ]);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // Act
        $result = $this->pvpStartUseCase->exec(
            $user,
            $sysPvpSeasonId,
            (bool)$isUseItem,
            $myId,
            $partyNo,
            $inGameBattleLog
        );

        // Assert
        $this->assertNotNull($result);

        // 挑戦回数が消費されていないことを確認
        $usrPvp = UsrPvp::query()
            ->where('usr_user_id', $usrUserId)
            ->where('sys_pvp_season_id', $sysPvpSeasonId)
            ->first();

        $this->assertNotNull($usrPvp);
        $this->assertEquals($initialChallengeCount, $usrPvp->getDailyRemainingChallengeCount());
        $this->assertEquals($initialItemChallengeCount, $usrPvp->getDailyRemainingItemChallengeCount());
    }

    private function setupMasterData(): void
    {
        // PVP挑戦アイテムIDを設定
        MstConfig::factory()->create([
            'key' => 'PVP_CHALLENGE_ITEM_ID',
            'value' => 'pvp_challenge_item',
        ]);

        // PVP挑戦アイテムのマスターデータを作成
        MstItem::factory()->create([
            'id' => 'pvp_challenge_item',
            'type' => 'Etc',
            'rarity' => 'R',
        ]);

        // テスト用のマスターユニットデータを作成
        MstUnit::factory()->create([
            'id' => 'unit_001',
            'color' => 'Red',
            'unit_label' => 'DropR',
            'min_hp' => 1000,
            'max_hp' => 5000,
            'min_attack_power' => 100,
            'max_attack_power' => 500,
        ]);

        MstUnit::factory()->create([
            'id' => 'unit_002',
            'color' => 'Blue',
            'unit_label' => 'DropSR',
            'min_hp' => 1200,
            'max_hp' => 5200,
            'min_attack_power' => 120,
            'max_attack_power' => 520,
        ]);
    }

    private function setupUserData(string $usrUserId): void
    {
        // テスト用のユーザーユニットデータを作成
        UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'unit_001',
            'level' => 50,
            'rank' => 5,
            'grade_level' => 3,
        ]);

        UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'unit_002',
            'level' => 45,
            'rank' => 4,
            'grade_level' => 2,
        ]);
    }
}
