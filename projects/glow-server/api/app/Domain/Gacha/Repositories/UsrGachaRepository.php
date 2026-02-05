<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Repositories;

use App\Domain\Gacha\Models\UsrGacha;
use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrGachaRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrGacha::class;

    /**
     * @param Collection<UsrGachaInterface> $models
     * @return void
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrGachaInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'opr_gacha_id' => $model->getOprGachaId(),
                'ad_played_at' => $model->getAdPlayedAt(),
                'played_at' => $model->getPlayedAt(),
                'ad_count' => $model->getAdCount(),
                'ad_daily_count' => $model->getAdDailyCount(),
                'count' => $model->getCount(),
                'daily_count' => $model->getDailyCount(),
                'expires_at' => $model->getExpiresAt(),
                'current_step_number' => $model->getCurrentStepNumber(),
                'loop_count' => $model->getLoopCount(),
            ];
        })->toArray();

        UsrGacha::upsert(
            $upsertValues,
            ['usr_user_id', 'opr_gacha_id'],
            [
                'ad_played_at',
                'played_at',
                'ad_count',
                'ad_daily_count',
                'count',
                'daily_count',
                'expires_at',
                'current_step_number',
                'loop_count',
            ],
        );
    }

    /**
     * @param string $usrUserId
     * @param string $oprGachaId
     *
     * @return UsrGachaInterface
     */
    public function create(
        string $usrUserId,
        string $oprGachaId
    ): UsrGachaInterface {
        $model = new UsrGacha();
        $model->init($usrUserId, $oprGachaId);
        $this->syncModel($model);
        return $model;
    }

    /**
     * @param string $usrUserId
     * @param string $oprGachaId
     *
     * @return UsrGachaInterface|null
     */
    public function getByOprGachaId(
        string $usrUserId,
        string $oprGachaId
    ): ?UsrGachaInterface {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'opr_gacha_id',
            $oprGachaId,
            function () use ($usrUserId, $oprGachaId) {
                return UsrGacha::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('opr_gacha_id', $oprGachaId)
                    ->first();
            }
        );
    }

    /**
     * @param string $usrUserId
     * @param Collection $oprGachaIds
     *
     * @return Collection<UsrGachaInterface>
     */
    public function getByOprGachaIds(
        string $usrUserId,
        Collection $oprGachaIds
    ): Collection {
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($oprGachaIds) {
                return $cache->filter(function (UsrGachaInterface $model) use ($oprGachaIds) {
                    return $oprGachaIds->contains($model->getOprGachaId());
                });
            },
            expectedCount: $oprGachaIds->count(),
            dbCallback: function () use ($usrUserId, $oprGachaIds) {
                return UsrGacha::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('opr_gacha_id', $oprGachaIds)
                    ->get();
            },
        );
    }
}
