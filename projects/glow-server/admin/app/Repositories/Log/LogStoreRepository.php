<?php

declare(strict_types=1);

namespace App\Repositories\Log;

use App\Models\Log\LogStore;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository as BaseLogStoreRepository;

class LogStoreRepository extends BaseLogStoreRepository
{
    protected string $modelClass = LogStore::class;

    public function fetchLogsByDateRange(
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        int $offset = 0,
        int $limit = 1000
    ): Collection {
        return $this->modelClass::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
}
