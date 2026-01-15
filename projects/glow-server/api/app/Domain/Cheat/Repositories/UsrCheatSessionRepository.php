<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Repositories;

use App\Domain\Cheat\Models\UsrCheatSession;
use App\Domain\Cheat\Models\UsrCheatSessionInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;

class UsrCheatSessionRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrCheatSession::class;

    /**
     * @param string $usrUserId
     * @return UsrCheatSessionInterface|null
     */
    public function findByUsrUserId(string $usrUserId): ?UsrCheatSessionInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    /**
     * @param string $usrUserId
     * @return UsrCheatSessionInterface
     */
    public function findOrCreate(string $usrUserId): UsrCheatSessionInterface
    {
        $usrCheatSession = $this->findByUsrUserId($usrUserId);
        if (is_null($usrCheatSession)) {
            $usrCheatSession = $this->create($usrUserId);
        }

        return $usrCheatSession;
    }

    /**
     * @param string $usrUserId
     * @return UsrCheatSessionInterface
     */
    public function create(string $usrUserId): UsrCheatSessionInterface
    {
        $usrCheatSession = new UsrCheatSession();
        $usrCheatSession->init($usrUserId);

        $this->syncModel($usrCheatSession);

        return $usrCheatSession;
    }
}
