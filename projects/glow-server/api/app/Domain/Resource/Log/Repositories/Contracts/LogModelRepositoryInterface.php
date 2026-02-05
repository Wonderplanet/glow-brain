<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Repositories\Contracts;

use Illuminate\Support\Collection;

interface LogModelRepositoryInterface
{
    public function saveModels(Collection $models): void;
}
