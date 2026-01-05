<?php

declare(strict_types=1);

namespace App\Repositories\Log;

use App\Domain\Gacha\Models\LogGacha;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LogGachaRepository
{
    protected string $modelClass = LogGacha::class;

    /**
     * 特定日付のガシャログを取得
     * @param CarbonImmutable $date
     * @param int             $limit
     * @param int             $offset
     * @return Collection
     */
    public function getByDateWithPagination(CarbonImmutable $date, int $limit, int $offset): Collection
    {
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();
        return $this->modelClass::query()
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('opr_gacha_id')
            ->orderBy('created_at')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }
}
