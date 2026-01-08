<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Outpost\Models\UsrOutpostInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrOutpostRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrOutpost::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrOutpost $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_outpost_id' => $model->getMstOutpostId(),
                'mst_artwork_id' => $model->getMstArtworkId(),
                'is_used' => $model->getIsUsed(),
            ];
        })->toArray();

        UsrOutpost::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_outpost_id'],
            ['mst_artwork_id', 'is_used'],
        );
    }

    public function create(string $usrUserId, string $mstOutpostId, int $isUsed = 0): UsrOutpostInterface
    {
        $model = new UsrOutpost();

        $model->usr_user_id = $usrUserId;
        $model->mst_outpost_id = $mstOutpostId;
        $model->is_used = $isUsed;

        $this->syncModel($model);

        return $model;
    }

    /**
     * @return Collection<UsrOutpostInterface>
     */
    public function getList(string $userId): Collection
    {
        return $this->cachedGetAll($userId);
    }

    public function findByMstOutpostId(
        string $usrUserId,
        string $mstOutpostId,
        bool $isThrowError = false
    ): ?UsrOutpostInterface {
        $usrOutpost = $this->cachedGetOneWhere(
            $usrUserId,
            'mst_outpost_id',
            $mstOutpostId,
            function () use ($usrUserId, $mstOutpostId) {
                return UsrOutpost::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_outpost_id', $mstOutpostId)
                    ->first();
            },
        );
        if ($isThrowError && $usrOutpost === null) {
            throw new GameException(
                ErrorCode::OUTPOST_NOT_OWNED,
                'usr_outpost not found mst_outpost_id: ' . $mstOutpostId
            );
        }
        return $usrOutpost;
    }

    public function getUsed(string $usrUserId): ?UsrOutpostInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'is_used',
            1,
            function () use ($usrUserId) {
                return UsrOutpost::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('is_used', 1)
                    ->first();
            },
        );
    }
}
