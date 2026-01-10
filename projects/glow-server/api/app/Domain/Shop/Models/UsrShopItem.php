<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $mst_shop_item_id
 * @property int    $trade_count
 * @property int    $trade_total_count
 * @property string $last_reset_at
 */
class UsrShopItem extends UsrEloquentModel implements UsrShopItemInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'id',
        'mst_shop_item_id',
        'trade_count',
        'trade_total_count',
        'last_reset_at',
    ];

    public function makeModelKey(): string
    {
        // UsrModelManagerのキャッシュ管理キーで、DBスキーマのユニークキーを使う
        return $this->usr_user_id . $this->mst_shop_item_id;
    }

    public function getMstShopItemId(): string
    {
        return $this->mst_shop_item_id;
    }

    public function getTradeCount(): int
    {
        return $this->trade_count;
    }

    public function getTradeTotalCount(): int
    {
        return $this->trade_total_count;
    }

    public function getLastResetAt(): string
    {
        return $this->last_reset_at;
    }

    public function reset(CarbonImmutable $now): void
    {
        $this->trade_count = 0;
        $this->last_reset_at = $now->format('Y-m-d H:i:s');
    }

    public function incrementTradeCount(): void
    {
        $this->trade_count++;
        $this->trade_total_count++;
    }
}
