<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Services\PvpTopService;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Http\Responses\Data\PvpHeldStatusData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PvpTopServiceTest extends TestCase
{
    private PvpTopService $pvpTopService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pvpTopService = $this->app->make(PvpTopService::class);
    }

    public function test_getPvpHeldStatus_正常系(): void
    {
        $mstPvp = MstPvp::factory()->create();
        $season = SysPvpSeason::factory()->create([
            'start_at' => CarbonImmutable::now()->subDay(),
            'end_at' => CarbonImmutable::now()->addDay(),
        ]);
        $entity = $season->toEntity();
        $result = $this->pvpTopService->getPvpHeldStatus($entity);
        $this->assertInstanceOf(PvpHeldStatusData::class, $result);
        $this->assertEquals($season->getId(), $result->getSysPvpSeasonId());
    }

    public static function provideCreateUsrPvpCases(): array
    {
        return [
            '最終プレイがある場合ランク引き継ぎ' => [
                [
                    'seasonIndex' => 1,
                    'attrs' => [
                        'pvp_rank_class_type' => PvpRankClassType::PLATINUM->value,
                        'pvp_rank_class_level' => 2,
                    ],
                ],
                2,
                PvpRankClassType::GOLD->value,
                1,
                5,
                3,
            ],
            '3シーズン離れた場合はランクが3段下がる' => [
                [
                    'seasonIndex' => 1,
                    'attrs' => [
                        'pvp_rank_class_type' => PvpRankClassType::PLATINUM->value,
                        'pvp_rank_class_level' => 2,
                    ],
                ],
                4,
                PvpRankClassType::BRONZE->value,
                1,
                5,
                3,
            ],
            '4シーズン以上離れた場合は初期値' => [
                [
                    'seasonIndex' => 1,
                    'attrs' => [
                        'pvp_rank_class_type' => PvpRankClassType::PLATINUM->value,
                        'pvp_rank_class_level' => 2,
                    ],
                ],
                5,
                PvpRankClassType::BRONZE->value,
                0,
                5,
                3,
            ],
            '最終プレイがない場合は初期値' => [
                null,
                3,
                PvpRankClassType::BRONZE->value,
                0,
                7,
                2,
            ],
            'ブロンズ０の場合は初期値' => [
                [
                    'seasonIndex' => 1,
                    'attrs' => [
                        'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                        'pvp_rank_class_level' => 0,
                    ],
                ],
                2,
                PvpRankClassType::BRONZE->value,
                0,
                5,
                3,
            ],
            'ブロンズ１の場合は初期値' => [
                [
                    'seasonIndex' => 1,
                    'attrs' => [
                        'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                        'pvp_rank_class_level' => 1,
                    ],
                ],
                2,
                PvpRankClassType::BRONZE->value,
                0,
                5,
                3,
            ],
            'ブロンズ２の場合はブロンズ１' => [
                [
                    'seasonIndex' => 1,
                    'attrs' => [
                        'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                        'pvp_rank_class_level' => 2,
                    ],
                ],
                2,
                PvpRankClassType::BRONZE->value,
                1,
                5,
                3,
            ],
            'ブロンズ３の場合はブロンズ１' => [
                [
                    'seasonIndex' => 1,
                    'attrs' => [
                        'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                        'pvp_rank_class_level' => 3,
                    ],
                ],
                2,
                PvpRankClassType::BRONZE->value,
                1,
                5,
                3,
            ],
            'ブロンズ４の場合はブロンズ１' => [
                [
                    'seasonIndex' => 1,
                    'attrs' => [
                        'pvp_rank_class_type' => PvpRankClassType::BRONZE->value,
                        'pvp_rank_class_level' => 4,
                    ],
                ],
                2,
                PvpRankClassType::BRONZE->value,
                1,
                5,
                3,
            ],
        ];
    }

    private function createMstPvpRanks(): Collection
    {
        return MstPvpRank::factory()->createMany([
            ['rank_class_type' => PvpRankClassType::BRONZE->value,   'rank_class_level' => 0, 'required_lower_score' => 0],
            ['rank_class_type' => PvpRankClassType::BRONZE->value,   'rank_class_level' => 1, 'required_lower_score' => 1],
            ['rank_class_type' => PvpRankClassType::SILVER->value,   'rank_class_level' => 1, 'required_lower_score' => 100],
            ['rank_class_type' => PvpRankClassType::GOLD->value,     'rank_class_level' => 1, 'required_lower_score' => 200],
            ['rank_class_type' => PvpRankClassType::PLATINUM->value, 'rank_class_level' => 1, 'required_lower_score' => 300],
        ])->groupBy(['rank_class_type', 'rank_class_level']);
    }

    #[DataProvider('provideCreateUsrPvpCases')]
    public function test_generateUsrPvpForNewSeason(
        ?array $lastPlayed,
        int $seasonCount,
        string $expectedRankType,
        int $expectedRankLevel,
        int $expectedChallengeCount,
        int $expectedItemChallengeCount
    ): void {
        $usrUserId = $this->createUsrUser()->getId();
        $seasons = [];
        foreach (range(1, $seasonCount) as $i) {
            $seasons[$i] = SysPvpSeason::factory()->create(['id' => '202500' . $i]);
        }
        $currentSysPvpSeason = $seasons[$seasonCount];
        $usrPvp = null;
        if ($lastPlayed) {
            $usrPvp = UsrPvp::factory()->create(array_merge([
                'usr_user_id' => $usrUserId,
                'sys_pvp_season_id' => $seasons[$lastPlayed['seasonIndex']]->getId(),
                'last_played_at' => CarbonImmutable::now()->subDays(10),
            ], $lastPlayed['attrs']));
        }
        $now = $this->fixTime();
        $mstPvpRanks = $this->createMstPvpRanks();
        $usrPvp = $this->pvpTopService->generateUsrPvpForNewSeason(
            $usrUserId,
            $currentSysPvpSeason->getId(),
            $expectedChallengeCount,
            $expectedItemChallengeCount,
            $usrPvp,
            $now
        );
        $mstPvpRank = $mstPvpRanks->get($usrPvp->getPvpRankClassType())->get($usrPvp->getPvpRankClassLevel())->first();
        $this->assertEquals($expectedRankType, $usrPvp->getPvpRankClassType());
        $this->assertEquals($expectedRankLevel, $usrPvp->getPvpRankClassLevel());
        $this->assertEquals($mstPvpRank->required_lower_score, $usrPvp->getScore());
        $this->assertEquals($expectedChallengeCount, $usrPvp->getDailyRemainingChallengeCount());
        $this->assertEquals($expectedItemChallengeCount, $usrPvp->getDailyRemainingItemChallengeCount());
    }

        #[DataProvider('provideCreateUsrPvpCases')]
    public function test_generateUsrPvpForNewSeason_received(
        ?array $lastPlayed,
        int $seasonCount,
        string $expectedRankType,
        int $expectedRankLevel,
        int $expectedChallengeCount,
        int $expectedItemChallengeCount
    ): void {
        $usrUserId = $this->createUsrUser()->getId();
        $seasons = [];
        foreach (range(1, $seasonCount) as $i) {
            $seasons[$i] = SysPvpSeason::factory()->create(['id' => '202500' . $i]);
        }
        $currentSysPvpSeason = $seasons[$seasonCount];
        $usrPvp = null;
        if ($lastPlayed) {
            $usrPvp = UsrPvp::factory()->create(array_merge([
                'usr_user_id' => $usrUserId,
                'sys_pvp_season_id' => $seasons[$lastPlayed['seasonIndex']]->getId(),
                'last_played_at' => CarbonImmutable::now()->subDays(10),
                'is_season_reward_received' => true,
            ], $lastPlayed['attrs']));
        }
        $now = $this->fixTime();
        $mstPvpRanks = $this->createMstPvpRanks();
        $usrPvp = $this->pvpTopService->generateUsrPvpForNewSeason(
            $usrUserId,
            $currentSysPvpSeason->getId(),
            $expectedChallengeCount,
            $expectedItemChallengeCount,
            $usrPvp,
            $now
        );
        $mstPvpRank = $mstPvpRanks->get($usrPvp->getPvpRankClassType())->get($usrPvp->getPvpRankClassLevel())->first();
        $this->assertEquals($expectedRankType, $usrPvp->getPvpRankClassType());
        $this->assertEquals($expectedRankLevel, $usrPvp->getPvpRankClassLevel());
        $this->assertEquals($mstPvpRank->required_lower_score, $usrPvp->getScore());
        $this->assertEquals($expectedChallengeCount, $usrPvp->getDailyRemainingChallengeCount());
        $this->assertEquals($expectedItemChallengeCount, $usrPvp->getDailyRemainingItemChallengeCount());
    }
}
