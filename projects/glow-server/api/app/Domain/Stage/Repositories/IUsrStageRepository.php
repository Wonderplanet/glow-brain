<?php

declare(strict_types=1);

namespace App\Domain\Stage\Repositories;

use App\Domain\Stage\Models\IBaseUsrStage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface IUsrStageRepository
{
    public function syncModel(IBaseUsrStage $model): void;

    /**
     * @param Collection<IBaseUsrStage> $models
     * @return void
     */
    public function syncModels(Collection $models): void;

    public function create(string $usrUserId, string $mstStageId, ?CarbonImmutable $now = null): IBaseUsrStage;

    public function findByMstStageId(string $usrUserId, string $mstStageId): ?IBaseUsrStage;

    /**
     * @return \Illuminate\Support\Collection<IBaseUsrStage>
     */
    public function findByMstStageIds(string $usrUserId, Collection $mstStageIds): Collection;
}
