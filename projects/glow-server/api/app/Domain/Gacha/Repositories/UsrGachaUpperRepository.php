<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Repositories;

use App\Domain\Gacha\Models\UsrGachaUpper;
use App\Domain\Gacha\Models\UsrGachaUpperInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrGachaUpperRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrGachaUpper::class;

    /**
     * @param Collection<UsrGachaUpperInterface> $models
     * @return void
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrGachaUpperInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'upper_group' => $model->getUpperGroup(),
                'upper_type' => $model->getUpperType(),
                'count' => $model->getCount(),
            ];
        })->toArray();

        UsrGachaUpper::upsert(
            $upsertValues,
            ['usr_user_id', 'upper_group', 'upper_type'],
            ['count'],
        );
    }

    /**
     * @param string $usrUserId
     * @param string $upperGroup
     * @param string $upperType
     * @return UsrGachaUpperInterface
     */
    public function create(string $usrUserId, string $upperGroup, string $upperType): UsrGachaUpperInterface
    {
        $model = new UsrGachaUpper();
        $model->init($usrUserId, $upperGroup, $upperType);

        $this->syncModel($model);

        return $model;
    }

    /**
     * @param string $usrUserId
     * @param string $upperGroup
     *
     * @return UsrGachaUpperInterface|null
     */
    public function getByUpperGroup(
        string $usrUserId,
        string $upperGroup
    ): ?UsrGachaUpperInterface {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'upper_group',
            $upperGroup,
            function () use ($usrUserId, $upperGroup) {
                return UsrGachaUpper::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('upper_group', $upperGroup)
                    ->first();
            },
        );
    }

    public function getByUpperGroupAndTypes(string $usrUserId, string $upperGroup, Collection $upperTypes): Collection
    {
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($upperGroup, $upperTypes) {
                return $cache->filter(function (UsrGachaUpperInterface $model) use ($upperGroup, $upperTypes) {
                    return $model->getUpperGroup() === $upperGroup && $upperTypes->contains($model->getUpperType());
                });
            },
            expectedCount: $upperTypes->count(),
            dbCallback: function () use ($usrUserId, $upperGroup, $upperTypes) {
                return UsrGachaUpper::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('upper_group', $upperGroup)
                    ->whereIn('upper_type', $upperTypes)
                    ->get();
            },
        );
    }

    /**
     * @param string $usrUserId
     * @param Collection $upperGroups
     *
     * @return Collection<UsrGachaUpperInterface>
     */
    public function getByUpperGroups(
        string $usrUserId,
        Collection $upperGroups
    ): Collection {
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($upperGroups) {
                return $cache->filter(function (UsrGachaUpperInterface $model) use ($upperGroups) {
                    return $upperGroups->contains($model->getUpperGroup());
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId, $upperGroups) {
                return UsrGachaUpper::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('upper_group', $upperGroups)
                    ->get();
            },
        )->values();
    }

    /**
     * ユーザーガシャ天井取得
     *
     * @param string $usrUserId
     * @param string $upperGroup
     *
     * @return UsrGachaUpperInterface
     */
    public function getOrCreate(string $usrUserId, string $upperGroup, string $upperType): UsrGachaUpperInterface
    {
        $usrGachaUpper = $this->getByUpperGroup($usrUserId, $upperGroup);
        if (is_null($usrGachaUpper)) {
            // 新規作成
            $usrGachaUpper = $this->create($usrUserId, $upperGroup, $upperType);
        }
        return $usrGachaUpper;
    }
}
