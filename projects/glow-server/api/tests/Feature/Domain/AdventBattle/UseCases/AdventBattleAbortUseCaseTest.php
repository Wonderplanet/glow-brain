<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Enums\AdventBattleSessionStatus;
use App\Domain\AdventBattle\Enums\LogAdventBattleResult;
use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\Models\UsrAdventBattleSessionInterface;
use App\Domain\AdventBattle\UseCases\AdventBattleAbortUseCase;
use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\CacheKeyUtil;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class AdventBattleAbortUseCaseTest extends TestCase
{
    private AdventBattleAbortUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = app(AdventBattleAbortUseCase::class);
    }

    /**
     * @dataProvider params_exec_正常実行
     */
    public function test_exec_正常実行(LogAdventBattleResult $abortType)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);
        $this->fixTime('2024-01-10 00:00:00');
        $mstAdventBattleId = "10";
        $partyNo = 5;

        UsrAdventBattleSession::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_advent_battle_id' => $mstAdventBattleId,
            'is_valid' => AdventBattleSessionStatus::STARTED->value,
            'party_no' => $partyNo,
        ]);

        $allUserTotalScore = 20000;
        $key = CacheKeyUtil::getAdventBattleRaidTotalScoreKey($mstAdventBattleId);
        Redis::connection()->set($key, $allUserTotalScore);

        // Exercise
        $response = $this->useCase->exec($currentUser, $abortType->value);

        // Verify
        $this->assertEquals($allUserTotalScore, $response->allUserTotalScore);

        /** @var UsrAdventBattleSessionInterface $usrAdventBattleSession */
        $usrAdventBattleSession = UsrAdventBattleSession::where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrUserId, $usrAdventBattleSession->getUsrUserId());
        $this->assertEquals($mstAdventBattleId, $usrAdventBattleSession->getMstAdventBattleId());
        $this->assertEquals(AdventBattleSessionStatus::CLOSED, $usrAdventBattleSession->getIsValid());
        $this->assertEquals($partyNo, $usrAdventBattleSession->getPartyNo());
    }

    public static function params_exec_正常実行()
    {
        return [
            'リタイア' => [LogAdventBattleResult::RETIRE],
            '中断復帰キャンセル' => [LogAdventBattleResult::CANCEL],
        ];
    }

    public function test_exec_セッション無し()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ADVENT_BATTLE_SESSION_MISMATCH);
        $this->expectExceptionMessage('usr_advent_battle_session is not found.');
        $this->useCase->exec($currentUser, LogAdventBattleResult::RETIRE->value);
    }
}
