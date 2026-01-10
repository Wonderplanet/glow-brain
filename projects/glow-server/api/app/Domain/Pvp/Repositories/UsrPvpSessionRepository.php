<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Models\UsrPvpSession;
use App\Domain\Pvp\Models\UsrPvpSessionInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;

// 1ユーザーあたりのレコード数が、最大でも1つの場合
class UsrPvpSessionRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrPvpSession::class;

    public function create(string $usrUserId, string $sysPvpSeasonId): UsrPvpSessionInterface
    {
        $model = new UsrPvpSession();
        $model->init($usrUserId, $sysPvpSeasonId);

        $this->syncModel($model);
        return $model;
    }

    public function find(string $usrUserId): ?UsrPvpSessionInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    public function findValidOne(string $usrUserId): ?UsrPvpSessionInterface
    {
        $usrPvpSession = $this->find($usrUserId);
        if ($usrPvpSession?->isStarted()) {
            return $usrPvpSession;
        }
        return null;
    }

    /**
     * 指定したユーザーとPVPシーズンのセッションを取得する
     */
    public function findOrCreate(
        string $usrUserId,
        string $sysPvpSeasonId,
    ): UsrPvpSessionInterface {
        $usrPvpSession = $this->find($usrUserId);

        if (is_null($usrPvpSession)) {
            return $this->create($usrUserId, $sysPvpSeasonId);
        }

        return $usrPvpSession;
    }

    public function findValidOneOrFail(string $usrUserId): UsrPvpSessionInterface
    {
        $usrPvpSession = $this->findValidOne($usrUserId);
        if ($usrPvpSession === null) {
            throw new GameException(
                ErrorCode::PVP_SESSION_NOT_FOUND,
                'PVP session not found or is not valid.',
            );
        }
        return $usrPvpSession;
    }

    public function findByUsrUserId(string $usrUserId): ?UsrPvpSessionInterface
    {
        return $this->find($usrUserId);
    }
}
