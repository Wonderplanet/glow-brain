<?php

namespace Tests\Feature\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Entities\AdventBattleInGameBattleLog;
use App\Domain\AdventBattle\Models\UsrAdventBattle;
use App\Domain\AdventBattle\Services\AdventBattleEndRaidService;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Entities\PartyStatus;
use App\Domain\Resource\Mst\Models\MstUnit;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdventBattleEndRaidServiceTest extends TestCase
{
    private AdventBattleEndRaidService $adventBattleEndRaidService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adventBattleEndRaidService = app(AdventBattleEndRaidService::class);
    }

    public static function params_updateScore_ランキングのスコアが登録される(): array
    {
        return [
            'ハイスコア更新なし' => [
                'beforeScore' => 100,
                'newScore' => 100,
                'isExcludedRanking' => 0,
                'expectedMaxScore' => 100,
                'expectedTotalScore' => 100 + 100,
                'expectedRankingScore' => 100,
            ],
            'ハイスコア更新あり(チート判定なし)' => [
                'beforeScore' => 100,
                'newScore' => 400,
                'isExcludedRanking' => 0,
                'expectedMaxScore' => 400,
                'expectedTotalScore' => 100 + 400,
                'expectedRankingScore' => 400,
            ],
            'ハイスコア更新あり(チート判定あり)' => [
                'beforeScore' => 100,
                'newScore' => 400,
                'isExcludedRanking' => 1,
                'expectedMaxScore' => 400,
                'expectedTotalScore' => 100 + 400,
                'expectedRankingScore' => AdventBattleConstant::RANKING_CHEATER_SCORE,
            ],
        ];
    }

    #[DataProvider('params_updateScore_ランキングのスコアが登録される')]
    public function testUpdateScore_ランキングのスコアが登録される(
        int $beforeScore,
        int $newScore,
        int $isExcludedRanking,
        int $expectedMaxScore,
        int $expectedTotalScore,
        int $expectedRankingScore,
    ) {
        // updateScoreはAdventBattleEndServiceで実装が完結しているがabstractクラスのため具象クラスであるAdventBattleEndRaidServiceでテストを行う
        // AdventBattleEndScoreChallengeServiceも挙動は同じなのでテストは省略する
        // Setup
        $mstAdventBattleId = 'advent1';
        $redisKey = CacheKeyUtil::getAdventBattleRankingKey($mstAdventBattleId);

        $usrUserId = $this->createUsrUser()->getId();
        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => $beforeScore,
            'total_score' => $beforeScore,
            'is_excluded_ranking' => $isExcludedRanking,
        ]);
        Redis::connection()->zadd($redisKey, [$usrUserId => $beforeScore]);

        $adventBattleInGameBattleLog = new AdventBattleInGameBattleLog(
            1,
            1,
            $newScore,
            collect([
                new PartyStatus(
                    'usrUnit1',
                    'unit1',
                    'Red',
                    'Attack',
                    1,
                    1,
                    1,
                    1,
                    1,
                    1,
                    '1001',
                    1,
                    1,
                    '2002',
                    '3002',
                    '4002',
                ),
            ]),
            99999999,
            collect(),
            collect(), // artworkPartyStatus
        );

        // Exercise
        $this->execPrivateMethod(
            $this->adventBattleEndRaidService,
            'updateScore',
            [$usrAdventBattle, $adventBattleInGameBattleLog, 1]
        );
        $this->saveAll();

        // Verify
        $usrAdventBattle = UsrAdventBattle::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();
        $this->assertEquals($expectedMaxScore, $usrAdventBattle->getMaxScore());
        $this->assertEquals($expectedTotalScore, $usrAdventBattle->getTotalScore());

        // スコアが加算されていること
        $score = Redis::connection()->zscore($redisKey, $usrUserId);
        $this->assertEquals($expectedRankingScore, $score);
    }

    public function testUpdateScore_ハイスコアを更新しない場合最高スコアパーティは更新されない()
    {
        // Setup
        $mstAdventBattleId = 'advent1';
        $partyNo = 1;
        $prevScore = 100;

        $usrUserId = $this->createUsrUser()->getId();
        $partyData = [
            ['mstUnitId' => 'unit1', 'level' => 1, 'gradeLevel' => 1],
        ];
        $usrAdventBattle = UsrAdventBattle::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'max_score' => $prevScore,
            'total_score' => $prevScore,
        ]);
        $adventBattleInGameBattleLog = new AdventBattleInGameBattleLog(
            1,
            1,
            99,
            collect([
                new PartyStatus(
                    'usrUnit1',
                    'unit1',
                    'Red',
                    'Attack',
                    1,
                    1,
                    1,
                    1,
                    1,
                    1,
                    '1001',
                    1,
                    1,
                    '2002',
                    '3002',
                    '4002',
                ),
            ]),
            99999999,
            collect(),
            collect(), // artworkPartyStatus
        );

        MstUnit::factory()->createMany([
            ['id' => 'unit1'],
            ['id' => 'unit2'],
            ['id' => 'unit3'],
        ]);
        UsrUnit::factory()->createMany([
            ['id' => 'unit1', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit1', 'level' => 1, 'grade_level' => 1],
            ['id' => 'unit2', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit2', 'level' => 2, 'grade_level' => 2],
            ['id' => 'unit3', 'usr_user_id' => $usrUserId, 'mst_unit_id' => 'unit3', 'level' => 3, 'grade_level' => 3],
        ]);
        UsrParty::factory()->create([
            'usr_user_id' => $usrUserId,
            'party_no' => $partyNo,
            'usr_unit_id_1' => 'unit1',
            'usr_unit_id_2' => 'unit2',
            'usr_unit_id_3' => 'unit3',
        ]);

        // Exercise
        $this->execPrivateMethod(
            $this->adventBattleEndRaidService,
            'updateScore',
            [$usrAdventBattle, $adventBattleInGameBattleLog, $partyNo]
        );
        $this->saveAll();

        // Verify
        $usrAdventBattle = UsrAdventBattle::query()
            ->where('usr_user_id', $usrUserId)
            ->where('mst_advent_battle_id', $mstAdventBattleId)
            ->first();

        $this->assertEquals($prevScore, $usrAdventBattle->getMaxScore());
    }
}
