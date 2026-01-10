<?php

declare(strict_types=1);

namespace App\Services\Datalake;

use Carbon\CarbonImmutable;

/**
 * データレイク転送コマンドサービス
 */
class DatalakeTransferCommandService
{
    public function __construct(
        private DatalakeTransferService $datalakeTransferService,
    ) { }

    public function exec(string $env, CarbonImmutable $executionTime): void
    {
        // 集計対象となる日付を取得
        $targetDate = $executionTime->subDay()->startOfDay();

        $this->datalakeTransferService->execTransfer($env, $targetDate);
    }
}
