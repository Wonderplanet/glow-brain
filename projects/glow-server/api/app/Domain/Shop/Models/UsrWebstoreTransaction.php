<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $transaction_id
 * @property int|null $order_id
 * @property int $is_sandbox
 * @property string $status
 * @property string|null $error_code
 * @property string|null $item_grant_status
 * @property string|null $bank_status
 * @property string|null $adjust_status
 * @property string $created_at
 * @property string $updated_at
 */
class UsrWebstoreTransaction extends UsrEloquentModel implements UsrWebstoreTransactionInterface
{
    use HasFactory;

    protected $fillable = [
        'id',
        'usr_user_id',
        'transaction_id',
        'order_id',
        'is_sandbox',
        'status',
        'error_code',
        'item_grant_status',
        'bank_status',
        'adjust_status',
    ];

    protected $casts = [
        'is_sandbox' => 'integer',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->transaction_id;
    }

    public function getTransactionId(): string
    {
        return $this->transaction_id;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setOrderId(int $orderId): void
    {
        $this->order_id = $orderId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setAdjustStatus(?string $adjustStatus): void
    {
        $this->adjust_status = $adjustStatus;
    }

    public function isSandbox(): bool
    {
        return $this->is_sandbox === 1;
    }
}
