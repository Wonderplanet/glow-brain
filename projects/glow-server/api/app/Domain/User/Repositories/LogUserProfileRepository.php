<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\User\Models\LogUserProfile;

class LogUserProfileRepository extends LogModelRepository
{
    protected string $modelClass = LogUserProfile::class;

    public function create(
        string $usrUserId,
        string $profileColumn,
        string $beforeValue,
        string $afterValue,
    ): LogUserProfile {
        $model = new LogUserProfile();
        $model->setUsrUserId($usrUserId);
        $model->setProfileColumn($profileColumn);
        $model->setBeforeValue($beforeValue);
        $model->setAfterValue($afterValue);

        $this->addModel($model);

        return $model;
    }
}
