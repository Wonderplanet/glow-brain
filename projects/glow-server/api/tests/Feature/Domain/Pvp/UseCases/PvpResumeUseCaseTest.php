<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Pvp;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Enums\PvpSessionStatus;
use App\Domain\Pvp\Models\SysPvpSeason;
use App\Domain\Pvp\Models\UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\UseCases\PvpEndUseCase;
use App\Domain\Resource\Mst\Models\MstPvp;
use App\Domain\Resource\Mst\Models\MstPvpRank;
use App\Domain\User\Models\UsrUserProfile;
use App\Http\Responses\ResultData\PvpEndResultData;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class PvpResumeUseCaseTest extends TestCase
{
    public function test_resume_success(): void
    {
        $user = $this->createUsrUser();
        $usrUserId = $user->getId();
        $now = $this->fixTime();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );

        $mstPvp = MstPvp::factory()->create()->toEntity();
        $season = SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => $now->subDay(),
            'end_at' => $now->addDay(),
        ])->toEntity();
        $session = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season->getId(),
            'is_valid' => 1,
            'battle_start_at' => $now->toDateTimeString(),
            'opponent_pvp_status' => json_encode([
                'pvpUserProfile' => [
                    'myId' => 'opponent1',
                    'name' => 'opponent',
                    'mstUnitId' => 'unit1',
                    'mstEmblemId' => 'emblem1',
                    'score' => 100,
                    'partyPvpUnits' => [],
                    'winAddPoint' => 0,
                ],
                'unitStatuses' => [],
                'usrOutpostEnhancements' => [],
                'usrEncyclopediaEffects' => [],
                'mstArtworkIds' => [],
            ]),
        ]);

        $useCase = $this->app->make(\App\Domain\Pvp\UseCases\PvpResumeUseCase::class);
        $result = $useCase->exec(new CurrentUser($usrUserId));
        $this->assertNotNull($result->getOpponentPvpStatus());
        $this->assertEquals('opponent1', $result->getOpponentPvpStatus()->getPvpUserProfile()->getMyId());
    }

    public function test_resume_outside_season_throws(): void
    {
        $user = $this->createUsrUser();
        $usrUserId = $user->getId();
        $now = $this->fixTime();
        $sysPvpSeasonId = sprintf(
            '%04d0%02d',
            $now->isoWeekYear,
            $now->isoWeek
        );
        $mstPvp = MstPvp::factory()->create()->toEntity();
        $season = SysPvpSeason::factory()->create([
            'id' => $sysPvpSeasonId,
            'start_at' => $now,
            'end_at' => $now->addDays(7),
        ])->toEntity();
        $session = UsrPvpSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'sys_pvp_season_id' => $season->getId(),
            'is_valid' => PvpSessionStatus::STARTED->value,
            'battle_start_at' => $now->subDays(9)->toDateTimeString(),
        ]);
        $useCase = $this->app->make(\App\Domain\Pvp\UseCases\PvpResumeUseCase::class);
        $this->expectException(\App\Domain\Common\Exceptions\GameException::class);
        $this->expectExceptionCode(ErrorCode::PVP_SEASON_PERIOD_OUTSIDE);
        $useCase->exec(new CurrentUser($usrUserId));
    }
}
