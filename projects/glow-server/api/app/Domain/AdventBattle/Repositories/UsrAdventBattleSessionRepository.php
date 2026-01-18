<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Repositories;

use App\Domain\AdventBattle\Models\UsrAdventBattleSession;
use App\Domain\AdventBattle\Models\UsrAdventBattleSessionInterface;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use Carbon\CarbonImmutable;

class UsrAdventBattleSessionRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrAdventBattleSession::class;

    /**
     * @param string $usrUserId
     * @return UsrAdventBattleSessionInterface|null
     */
    public function findByUsrUserId(string $usrUserId): ?UsrAdventBattleSessionInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    /**
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return UsrAdventBattleSessionInterface
     */
    public function findOrCreate(string $usrUserId, CarbonImmutable $now): UsrAdventBattleSessionInterface
    {
        $usrAdventBattleSession = $this->findByUsrUserId($usrUserId);
        if (is_null($usrAdventBattleSession)) {
            $usrAdventBattleSession = $this->create($usrUserId, $now);
        }

        return $usrAdventBattleSession;
    }

    /**
     * @param string $usrUserId
     * @param bool $isThrowError
     * @return UsrAdventBattleSessionInterface|null
     * @throws GameException
     */
    public function findWithError(string $usrUserId, bool $isThrowError = false): ?UsrAdventBattleSessionInterface
    {
        $usrAdventBattleSession = $this->findByUsrUserId($usrUserId);
        if ($isThrowError && is_null($usrAdventBattleSession)) {
            throw new GameException(
                ErrorCode::ADVENT_BATTLE_SESSION_MISMATCH,
                'usr_advent_battle_session is not found.',
            );
        }

        return $usrAdventBattleSession;
    }

    /**
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return UsrAdventBattleSessionInterface
     */
    public function create(string $usrUserId, CarbonImmutable $now): UsrAdventBattleSessionInterface
    {
        $usrAdventBattleSession = new UsrAdventBattleSession();
        $usrAdventBattleSession->init($usrUserId, $now);

        $this->syncModel($usrAdventBattleSession);

        return $usrAdventBattleSession;
    }
}
