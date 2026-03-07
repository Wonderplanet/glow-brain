<?php

declare(strict_types=1);

namespace App\Domain\Stage\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\Stage\Models\UsrStageSession;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use Carbon\CarbonImmutable;

class UsrStageSessionRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrStageSession::class;

    public function findByUsrUserId(string $usrUserId): ?UsrStageSessionInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    /**
     * 対象ユーザーのレコードを取得する。存在しない場合は作成する
     */
    public function get(string $usrUserId, CarbonImmutable $now): UsrStageSessionInterface
    {
        $usrStageSession = $this->findByUsrUserId($usrUserId);
        if (is_null($usrStageSession)) {
            $usrStageSession = $this->create($usrUserId, $now);
        }

        return $usrStageSession;
    }

    public function create(string $usrUserId, CarbonImmutable $now): UsrStageSessionInterface
    {
        $usrStageSession = new UsrStageSession();
        $usrStageSession->init($usrUserId, $now);

        $this->syncModel($usrStageSession);

        return $usrStageSession;
    }
}
