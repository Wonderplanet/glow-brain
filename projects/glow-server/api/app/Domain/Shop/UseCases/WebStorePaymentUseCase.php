<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use Illuminate\Support\Facades\Log;

/**
 * WebStore W4: 支払い通知
 *
 * notification_type: payment
 */
class WebStorePaymentUseCase
{
    /**
     * W4: 支払い通知
     *
     * @param string $transactionId トランザクションID
     * @param bool $isSandbox サンドボックスモードか
     * @param int $dryRun ドライラン値
     * @return void
     */
    public function exec(string $transactionId, bool $isSandbox, int $dryRun): void
    {
        Log::info('WebStore payment notification received', [
            'transaction_id' => $transactionId,
            'is_sandbox' => $isSandbox,
            'dry_run' => $dryRun,
        ]);
    }
}
