<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Repositories;

use App\Domain\Cheat\Models\LogSuspectedUser;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use Carbon\CarbonImmutable;

class LogSuspectedUserRepository extends LogModelRepository
{
    protected string $modelClass = LogSuspectedUser::class;

    /**
     * @param string $usrUserId
     * @param string $contentType
     * @param string|null $targetId
     * @param string $cheatType
     * @param array<mixed> $detail
     * @param CarbonImmutable $suspectedAt
     */
    public function create(
        string $usrUserId,
        string $contentType,
        ?string $targetId,
        string $cheatType,
        array $detail,
        CarbonImmutable $suspectedAt,
    ): void {
        $model = new LogSuspectedUser();

        $model->setUsrUserId($usrUserId);
        $model->setContentType($contentType);
        $model->setTargetId($targetId);
        $model->setCheatType($cheatType);
        $model->setDetail($detail);
        $model->setSuspectedAt($suspectedAt);

        $this->addModel($model);
    }
}
