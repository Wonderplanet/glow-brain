<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\Tutorial\Models\LogTutorialAction;

class LogTutorialActionRepository extends LogModelRepository
{
    protected string $modelClass = LogTutorialAction::class;

    public function create(
        string $usrUserId,
        string $tutorialName
    ): LogTutorialAction {
        $model = new LogTutorialAction();
        $model->setUsrUserId($usrUserId);
        $model->setTutorialName($tutorialName);

        $this->addModel($model);

        return $model;
    }
}
