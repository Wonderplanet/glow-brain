<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Repositories\Contracts;

use Illuminate\Support\Collection;

interface UsrModelCacheRepositoryInterface
{
    public function saveModelsByUsrModelManager(Collection $models): void;
}
