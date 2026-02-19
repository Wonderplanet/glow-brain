<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\User\Models\LogLogin;

class LogLoginRepository extends LogModelRepository
{
    protected string $modelClass = LogLogin::class;

    public function create(
        string $usrUserId,
        int $loginCount,
        bool $isDayFirstLogin,
        int $loginDayCount,
        int $loginContinueDayCount,
        int $comebackDayCount,
    ): LogLogin {
        $model = new LogLogin();
        $model->setUsrUserId($usrUserId);
        $model->setLoginCount($loginCount);
        $model->setIsDayFirstLogin($isDayFirstLogin);
        $model->setLoginDayCount($loginDayCount);
        $model->setLoginContinueDayCount($loginContinueDayCount);
        $model->setComebackDayCount($comebackDayCount);

        $this->addModel($model);

        return $model;
    }
}
