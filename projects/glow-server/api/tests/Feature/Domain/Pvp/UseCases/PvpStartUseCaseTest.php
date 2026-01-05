<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp\UseCases;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\UseCases\PvpStartUseCase;
use App\Domain\Resource\Mst\Models\MstOutpost;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Domain\User\Models\UsrUserProfile;
use Tests\TestCase;

class PvpStartUseCaseTest extends TestCase
{
    private PvpStartUseCase $pvpStartUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pvpStartUseCase = app(PvpStartUseCase::class);
    }

    public function testExec_PVPセッションが正常に開始されることを確認する(): void
    {
        // Arrange
        $usrUserId = 'user_123';
        $this->setUsrUserId($usrUserId);

        $user = new CurrentUser($usrUserId);
        $sysPvpSeasonId = 'default_pvp';
        $myId = 'my_id_123';
        $partyNo = 1;
        $inGameBattleLog = [];
        $isUseItem = 0;

        // PVPシーズンを作成
        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        // MstPvpを作成
        MstPvp::factory()->create([
            'id' => 'default_pvp',
        ]);

        // テスト用のマスターユニットデータを作成
        MstUnit::factory()->create([
            'id' => 'test_unit_001',
            'color' => 'Red',
            'unit_label' => 'DropR',
            'min_hp' => 1000,
            'max_hp' => 5000,
            'min_attack_power' => 100,
            'max_attack_power' => 500,
        ]);

        MstUnit::factory()->create([
            'id' => 'test_unit_002',
            'color' => 'Blue',
            'unit_label' => 'DropSR',
            'min_hp' => 1200,
            'max_hp' => 5200,
            'min_attack_power' => 120,
            'max_attack_power' => 520,
        ]);

        // テスト用のユーザーユニットデータを作成
        $usrUnit1 = UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'test_unit_001',
            'level' => 50,
            'rank' => 5,
            'grade_level' => 3,
        ]);

        $usrUnit2 = UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'test_unit_002',
            'level' => 45,
            'rank' => 4,
            'grade_level' => 2,
        ]);

        // テスト用のパーティデータを作成
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'party_no' => $partyNo,
            'party_name' => "テストパーティ$partyNo",
            'usr_unit_id_1' => $usrUnit1->id,
            'usr_unit_id_2' => $usrUnit2->id,
        ]);

        // テスト用の前哨基地マスターデータを作成
        MstOutpost::factory()->create([
            'id' => 'outpost_001',
        ]);

        // テスト用の前哨基地強化マスターデータを作成
        MstOutpostEnhancement::factory()->create([
            'id' => 'outpost_enhancement_001',
            'mst_outpost_id' => 'outpost_001',
        ]);

        // テスト用の前哨基地強化データを作成
        UsrOutpostEnhancement::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_id' => 'outpost_001',
            'mst_outpost_enhancement_id' => 'outpost_enhancement_001',
            'level' => 3,
        ]);

        // テスト用のユーザープロフィールデータを作成
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // テスト用のUsrPvpデータを作成（selected_opponent_candidatesをJSON文字列で設定）
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
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'daily_remaining_challenge_count' => 5,
            'daily_remaining_item_challenge_count' => 3,
            'score' => 1000,
            'selected_opponent_candidates' => $selectedOpponentCandidates,
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
        $this->assertNotNull($result->getOpponentPvpStatus());

        // PVPセッションがDBに保存されていることを確認
        $usrPvpSession = UsrPvpSession::query()
            ->where('usr_user_id', $usrUserId)
            ->first();

        $this->assertNotNull($usrPvpSession);
        $this->assertEquals($usrUserId, $usrPvpSession->getUsrUserId());
        $this->assertEquals((string)$sysPvpSeasonId, $usrPvpSession->getSysPvpSeasonId());
        $this->assertEquals($partyNo, $usrPvpSession->getPartyNo());
        $this->assertEquals(PvpSessionStatus::STARTED, $usrPvpSession->getIsValid());
        $this->assertNotNull($usrPvpSession->getOpponentMyId());
        $this->assertNotNull($usrPvpSession->getOpponentPvpStatus());
        $this->assertEquals(1200, $usrPvpSession->getOpponentScore()); // selected_opponent_candidatesのスコアを使用

        // opponent_pvp_statusがJSONとして正しく保存されていることを確認
        $opponentPvpStatus = json_decode($usrPvpSession->getOpponentPvpStatus(), true);
        $this->assertIsArray($opponentPvpStatus);
        $this->assertArrayHasKey('unitStatuses', $opponentPvpStatus);
        $this->assertArrayHasKey('usrOutpostEnhancements', $opponentPvpStatus);
        $this->assertArrayHasKey('usrEncyclopediaEffects', $opponentPvpStatus);
        $this->assertArrayHasKey('mstArtworkIds', $opponentPvpStatus);
    }

    public function testExec_複数回実行した場合に既存セッションが更新されることを確認する(): void
    {
        // Arrange
        $usrUserId = 'user_456';
        $this->setUsrUserId($usrUserId);

        $user = new CurrentUser($usrUserId);
        $sysPvpSeasonId = 'default_pvp';
        $myId = 'my_id_456';
        $partyNo = 2;
        $inGameBattleLog = [];
        $isUseItem = 0;

        // PVPシーズンを作成
        SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        // MstPvpを作成
        MstPvp::factory()->create([
            'id' => 'default_pvp',
        ]);

        // テスト用のマスターユニットデータを作成
        MstUnit::factory()->create([
            'id' => 'unit_003',
            'color' => 'Green',
            'unit_label' => 'DropSSR',
            'min_hp' => 1300,
            'max_hp' => 5300,
            'min_attack_power' => 130,
            'max_attack_power' => 530,
        ]);

        // テスト用のユーザーユニットデータを作成
        $usrUnit3 = UsrUnit::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => 'unit_003',
            'level' => 60,
            'rank' => 6,
            'grade_level' => 4,
        ]);

        // テスト用のパーティデータを作成
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'party_no' => $partyNo,
            'party_name' => "テストパーティ$partyNo",
            'usr_unit_id_1' => $usrUnit3->id,
        ]);

        // テスト用のユーザープロフィールデータを作成
        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        // テスト用のUsrPvpデータを作成（selected_opponent_candidatesをJSON文字列で設定）
        $selectedOpponentCandidatesArray = [
            $myId => [
                'pvpUserProfile' => [
                    'myId' => $myId,
                    'name' => 'テスト対戦相手2',
                    'mstUnitId' => 'unit_003',
                    'mstEmblemId' => 'emblem_002',
                    'score' => 1300,
                    'mstUnitIds' => ['unit_003'],
                    'winAddPoint' => 60,
                ],
                'unitStatuses' => [
                    [
                        'mstUnitId' => 'unit_003',
                        'level' => 70,
                        'rank' => 7,
                        'gradeLevel' => 4,
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
            'sys_pvp_season_id' => $sysPvpSeasonId,
            'daily_remaining_challenge_count' => 5,
            'daily_remaining_item_challenge_count' => 3,
            'score' => 1000,
            'selected_opponent_candidates' => $selectedOpponentCandidates,
        ]);

        // Act - 最初の実行
        $result1 = $this->pvpStartUseCase->exec(
            $user,
            $sysPvpSeasonId,
            (bool)$isUseItem,
            $myId,
            $partyNo,
            $inGameBattleLog
        );

        // Act - 二回目の実行
        $result2 = $this->pvpStartUseCase->exec(
            $user,
            $sysPvpSeasonId,
            (bool)$isUseItem,
            $myId,
            $partyNo + 1, // 異なるパーティ番号
            $inGameBattleLog
        );

        // Assert
        $this->assertNotNull($result1);
        $this->assertNotNull($result2);

        // PVPセッションが1つだけ存在することを確認（新しく作成されるのではなく更新される）
        $usrPvpSessions = UsrPvpSession::query()
            ->where('usr_user_id', $usrUserId)
            ->get();

        $this->assertCount(1, $usrPvpSessions);

        $usrPvpSession = $usrPvpSessions->first();
        $this->assertEquals($partyNo + 1, $usrPvpSession->getPartyNo()); // 最新の値で更新されている
        $this->assertEquals(PvpSessionStatus::STARTED, $usrPvpSession->getIsValid());
    }

    #[DataProvider('params_testExec_不正な状態の場合にエラーとなることを確認する')]
    public function testExec_不正な状態の場合にエラーとなることを確認する(
        bool $shouldCreateSeason,
        ?int $seasonStartDaysOffset,
        ?int $seasonEndDaysOffset,
        bool $shouldCreateMstPvp,
        bool $shouldCreateUsrPvp
    ): void {
        // Setup
        $now = $this->fixTime('2025-10-15 07:00:00');
        $usrUserId = $this->createUsrUser()->id;
        $user = new CurrentUser($usrUserId);
        $sysPvpSeasonId = '2025042';

        // シーズンを作成する場合
        if ($shouldCreateSeason) {
            SysPvpSeason::factory()->create([
                'id' => $sysPvpSeasonId,
                'start_at' => $now->addDays($seasonStartDaysOffset),
                'end_at' => $now->addDays($seasonEndDaysOffset),
            ]);
        }

        // MstPvpを作成する場合
        if ($shouldCreateMstPvp) {
            MstPvp::factory()->create([
                'id' => $sysPvpSeasonId,
            ]);
        }

        // UsrPvpを作成する場合（今回のテストケースでは全て作成しない）
        if ($shouldCreateUsrPvp) {
            UsrPvp::factory()->create([
                'usr_user_id' => $usrUserId,
                'sys_pvp_season_id' => $sysPvpSeasonId,
                'daily_remaining_challenge_count' => 5,
                'daily_remaining_item_challenge_count' => 3,
                'score' => 1000,
                'selected_opponent_candidates' => [],
            ]);
        }

        $this->expectException(\App\Domain\Common\Exceptions\GameException::class);
        $this->expectExceptionCode(\App\Domain\Common\Constants\ErrorCode::PVP_SEASON_PERIOD_OUTSIDE);

        // Exercise
        $this->pvpStartUseCase->exec(
            $user,
            $sysPvpSeasonId,
            false,
            'opponent_my_id',
            1,
            [],
        );

        // Verify
        // 例外が発生するため、ここには到達しない
    }

    public static function params_testExec_不正な状態の場合にエラーとなることを確認する(): array
    {
        return [
            'シーズンが存在しない' => [
                'shouldCreateSeason' => false,
                'seasonStartDaysOffset' => null,
                'seasonEndDaysOffset' => null,
                'shouldCreateMstPvp' => false,
                'shouldCreateUsrPvp' => false,
            ],
            'シーズンが期間外（過去）' => [
                'shouldCreateSeason' => true,
                'seasonStartDaysOffset' => -10,
                'seasonEndDaysOffset' => -5,
                'shouldCreateMstPvp' => false,
                'shouldCreateUsrPvp' => false,
            ],
            'usr_pvpsが存在しない' => [
                'shouldCreateSeason' => true,
                'seasonStartDaysOffset' => -1,
                'seasonEndDaysOffset' => 1,
                'shouldCreateMstPvp' => true,
                'shouldCreateUsrPvp' => false,
            ],
        ];
    }
}
