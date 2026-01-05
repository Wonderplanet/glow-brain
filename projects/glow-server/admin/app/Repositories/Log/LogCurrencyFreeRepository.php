<?php

declare(strict_types=1);

namespace App\Repositories\Log;

use App\Models\Log\LogCurrencyFree;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Currency\Repositories\LogCurrencyFreeRepository as BaseLogCurrencyFreeRepository;

class LogCurrencyFreeRepository extends BaseLogCurrencyFreeRepository
{
    protected string $modelClass = LogCurrencyFree::class;

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
