<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Repositories;

use Illuminate\Support\Collection;

abstract class UsrModelNoCacheRepository extends UsrModelRepository
{
    public function syncModels(Collection $models): void
    {
        $models = $models->filter(function ($model) {
            return $this->isValidModel($model);
        });

        $this->saveModels($models);
    }
}
