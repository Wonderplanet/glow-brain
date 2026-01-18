<?php

declare(strict_types=1);

namespace App\Domain\Mission\Repositories;

use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Mission\Models\UsrMissionStatusInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use Carbon\CarbonImmutable;

class UsrMissionStatusRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrMissionStatus::class;

    /**
     * @param string $usrUserId
     * @return UsrMissionStatusInterface|null
     */
    public function get(string $usrUserId): ?UsrMissionStatusInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    /**
     * @param string $usrUserId
     * @return UsrMissionStatusInterface
     */
    public function create(string $usrUserId): UsrMissionStatusInterface
    {
        $model = new UsrMissionStatus();

        $model->usr_user_id = $usrUserId;
        $model->beginner_mission_status = MissionBeginnerStatus::HAS_LOCKED->value;
        $model->latest_mst_hash = '';
        $model->mission_unlocked_at = null;

        $this->syncModel($model);

        return $model;
    }

    /**
     * @param string $usrUserId
     * @return UsrMissionStatusInterface
     */
    public function getOrCreate(string $usrUserId): UsrMissionStatusInterface
    {
        $model = $this->get($usrUserId);
        if ($model === null) {
            $model = $this->create($usrUserId);
        }

        $this->syncModel($model);

        return $model;
    }



    /**
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return UsrMissionStatusInterface
     */
    public function setUnlockMission(string $usrUserId, CarbonImmutable $now): UsrMissionStatusInterface
    {
        $model = $this->getOrCreate($usrUserId);
        $model->setMissionUnlockedAt($now);

        $this->syncModel($model);

        return $model;
    }
}
