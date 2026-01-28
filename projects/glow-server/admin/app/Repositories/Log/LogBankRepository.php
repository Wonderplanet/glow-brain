<?php

declare(strict_types=1);

namespace App\Repositories\Log;

use App\Models\Log\LogBank;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LogBankRepository
{
    protected string $modelClass = LogBank::class;

    public function fetchLogsByDateRange(
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        int $offset = 0,
        int $limit = 1000
    ): Collection {
        // 2025-09-29 21:00:00より前の場合は、usr_user_idでユニークにするクエリを実行
        // $targetDateTime = CarbonImmutable::parse('2025-09-29 21:00:00');
        // if ($endDate->isBefore($targetDateTime)) {
        //     $sql = "
        //         SELECT * FROM (
        //             SELECT *,
        //                 ROW_NUMBER() OVER (PARTITION BY usr_user_id ORDER BY created_at ASC) as rn
        //             FROM log_banks
        //             WHERE created_at BETWEEN ? AND ?
        //         ) ranked
        //         WHERE rn = 1
        //         ORDER BY created_at ASC
        //         LIMIT ? OFFSET ?
        //     ";

        //     $results = DB::connection($this->modelClass::query()->getConnection()->getName())
        //         ->select($sql, [
        //             $startDate->toDateTimeString(),
        //             $endDate->toDateTimeString(),
        //             $limit,
        //             $offset
        //         ]);

        //     // DB::selectの結果をLogBankモデルのCollectionに変換
        //     LogBank::unguard();
        //     return collect($results)->map(function ($row) {
        //         $logBank = new LogBank();
        //         $logBank->fill((array) $row);
        //         $logBank->exists = true;
        //         return $logBank;
        //     });
        // }

        // 既存の処理（2025-09-29 21:00:00以降の場合）
        return $this->modelClass::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->orderBy('id')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
}
