<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\Tutorial\Models\UsrTutorialGacha;
use App\Domain\Tutorial\Models\UsrTutorialGachaInterface;

class UsrTutorialGachaRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrTutorialGacha::class;

    public function create(string $usrUserId): UsrTutorialGachaInterface
    {
        $model = new UsrTutorialGacha();

        $model->usr_user_id = $usrUserId;
        $model->gacha_result_json = json_encode([]);
        $model->confirmed_at = null;

        $this->syncModel($model);

        return $model;
    }

    public function get(string $usrUserId): ?UsrTutorialGachaInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    public function getOrCreate(string $usrUserId): UsrTutorialGachaInterface
    {
        $model = $this->get($usrUserId);
        if ($model === null) {
            $model = $this->create($usrUserId);
        }

        return $model;
    }
}
