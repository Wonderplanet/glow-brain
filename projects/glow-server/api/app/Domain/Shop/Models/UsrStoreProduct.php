<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $product_sub_id
 * @property int    $purchase_count
 * @property int    $purchase_total_count
 * @property string $last_reset_at
 */
class UsrStoreProduct extends UsrEloquentModel implements UsrStoreProductInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'id',
        'product_sub_id',
        'purchase_count',
        'purchase_total_count',
        'last_reset_at',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->product_sub_id;
    }

    public function getProductSubId(): string
    {
        return $this->product_sub_id;
    }

    public function getPurchaseCount(): int
    {
        return $this->purchase_count;
    }

    public function getPurchaseTotalCount(): int
    {
        return $this->purchase_total_count;
    }

    public function incrementPurchaseCount(): void
    {
        $this->purchase_count++;
        $this->purchase_total_count++;
    }

    public function getLastResetAt(): string
    {
        return $this->last_reset_at;
    }
}
