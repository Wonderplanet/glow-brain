<?php

declare(strict_types=1);

namespace App\Domain\GooglePlay\Repositories;

use App\Domain\Common\Entities\Clock;
use App\Domain\GooglePlay\Models\LogGooglePlayRefund;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LogGooglePlayRefundRepository
{
    public function create(
        string $transactionId,
        int $price,
        string $refundAt,
        string $purchaseToken
    ): LogGooglePlayRefund {
        $logGooglePlayRefund = new LogGooglePlayRefund();
        $logGooglePlayRefund->transaction_id = $transactionId;
        $logGooglePlayRefund->price = $price;
        $logGooglePlayRefund->refunded_at = $refundAt;
        $logGooglePlayRefund->purchase_token = $purchaseToken;
        return $logGooglePlayRefund;
    }

    /**
     * @param Collection      $logGooglePlayRefunds
     * @param CarbonImmutable $now
     */
    public function bulkCreate(Collection $logGooglePlayRefunds, CarbonImmutable $now): void
    {
        $datetime = $now->format(Clock::DATETIME_FORMAT);
        $values = $logGooglePlayRefunds->map(function (LogGooglePlayRefund $model) use ($datetime) {
            return [
                'id' => $model->getId(),
                'transaction_id' => $model->getTransactionId(),
                'price' => $model->getPrice(),
                'refunded_at' => $model->getRefundedAt(),
                'purchase_token' => $model->getPurchaseToken(),
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ];
        })->toArray();

        LogGooglePlayRefund::query()->insert($values);
    }
}
