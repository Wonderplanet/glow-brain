<?php

declare(strict_types=1);

namespace App\Domain\Unit\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\Unit\Models\UsrUnitSummary;
use App\Domain\Unit\Models\UsrUnitSummaryInterface;

class UsrUnitSummaryRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUnitSummary::class;

    /**
     * レコード取得
     * @param string $usrUserId
     * @return UsrUnitSummaryInterface|null
     */
    public function findByUsrUserId(string $usrUserId)
    {
        return $this->cachedGetOne($usrUserId);
    }

    /**
     * レコード作成
     * @param string $usrUserId
     * @return UsrUnitSummaryInterface
     */
    public function create(string $usrUserId): UsrUnitSummaryInterface
    {
        $model = new UsrUnitSummary();

        $model->usr_user_id = $usrUserId;
        $model->grade_level_total_count = 0;

        $this->syncModel($model);

        return $model;
    }

    /**
     * レコード取得してなければ作成
     * @param string $usrUserId
     * @return UsrUnitSummaryInterface
     */
    public function getOrCreate(string $usrUserId): UsrUnitSummaryInterface
    {
        $model = $this->findByUsrUserId($usrUserId);

        if ($model === null) {
            $model = $this->create($usrUserId);
        }
        return $model;
    }

    /**
     * GradeLevelTotalCountを取得（なければ作成）
     * @param string $usrUserId
     * @return int
     */
    public function getGradeLevelTotalCount(string $usrUserId): int
    {
        $model = $this->getOrCreate($usrUserId);
        return $model->getGradeLevelTotalCount();
    }
}
