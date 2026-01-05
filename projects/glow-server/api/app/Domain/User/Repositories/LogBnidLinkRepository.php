<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\User\Enums\BnidLinkActionType;
use App\Domain\User\Models\LogBnidLink;

class LogBnidLinkRepository extends LogModelRepository
{
    protected string $modelClass = LogBnidLink::class;

    public function create(
        string $usrUserId,
        BnidLinkActionType $actionType,
        ?string $beforeBnUserId,
        ?string $afterBnUserId,
        ?string $usrDeviceId,
        string $osPlatform
    ): LogBnidLink {
        $model = new LogBnidLink();
        $model->setUsrUserId($usrUserId);
        $model->setActionType($actionType);
        $model->setBeforeBnUserId($beforeBnUserId);
        $model->setAfterBnUserId($afterBnUserId);
        $model->setUsrDeviceId($usrDeviceId);
        $model->setOsPlatform($osPlatform);

        $this->addModel($model);

        return $model;
    }
}
