<?php

declare(strict_types=1);

namespace App\Domain\Common\Repositories;

use App\Domain\Common\Models\LogAdFreePlay;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use Carbon\CarbonImmutable;

class LogAdFreePlayRepository extends LogModelRepository
{
    protected string $modelClass = LogAdFreePlay::class;

    public function create(
        string $usrUserId,
        string $contentType,
        string $targetId,
        CarbonImmutable $playAt
    ): LogAdFreePlay {
        $model = new LogAdFreePlay();
        $model->setUsrUserId($usrUserId);
        $model->setContentType($contentType);
        $model->setTargetId($targetId);
        $model->setPlayAt($playAt);

        $this->addModel($model);

        return $model;
    }
}
